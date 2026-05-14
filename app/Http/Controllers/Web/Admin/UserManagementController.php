<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(['all', 'renter', 'provider', 'admin'])],
            'status' => ['nullable', Rule::in(['all', 'active', 'banned'])],
            'verification' => ['nullable', Rule::in(['all', 'pending', 'verified', 'rejected'])],
        ]);

        $users = User::query()
            ->with('provider:id,user_id,verification_status')
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $term = $filters['search'];
                $query->where(function ($nestedQuery) use ($term): void {
                    $nestedQuery
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when(! empty($filters['role']) && $filters['role'] !== 'all', fn ($query) => $query->where('role', $filters['role']))
            ->when(! empty($filters['status']) && $filters['status'] !== 'all', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->when(! empty($filters['verification']) && $filters['verification'] !== 'all', function ($query) use ($filters): void {
                $query->whereHas('provider', fn ($providerQuery) => $providerQuery->where('verification_status', $filters['verification']));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (User $user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_photo' => $user->profile_photo,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'banned_reason' => $user->banned_reason,
                    'banned_at' => $user->banned_at?->toDateTimeString(),
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'provider' => $user->provider ? [
                        'verification_status' => $user->provider->verification_status,
                    ] : null,
                ];
            });

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'banned_users' => User::where('is_active', false)->count(),
            'total_providers' => User::where('role', 'provider')->count(),
            'verified_providers' => DB::table('providers')->where('verification_status', 'verified')->count(),
            'pending_providers' => DB::table('providers')->where('verification_status', 'pending')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => array_merge([
                'search' => '',
                'role' => 'all',
                'status' => 'all',
                'verification' => 'all',
            ], $filters),
            'stats' => $stats,
        ]);
    }

    public function show(User $user): Response
    {
        $user->load([
            'provider.locations',
            'provider.items' => fn ($query) => $query->withCount('bookings')->latest()->limit(8),
            'addresses',
            'measurement',
        ]);

        $user->loadCount('bookings');

        $providerId = $user->provider?->id;
        $recentActivity = DB::table('booking_timeline as timeline')
            ->join('bookings', 'bookings.id', '=', 'timeline.booking_id')
            ->where(function ($query) use ($user, $providerId): void {
                $query->where('bookings.user_id', $user->id);
                if ($providerId) {
                    $query->orWhere('bookings.provider_id', $providerId);
                }
            })
            ->orderByDesc('timeline.created_at')
            ->limit(12)
            ->get([
                'timeline.id',
                'timeline.status',
                'timeline.notes',
                'timeline.created_at',
                'bookings.booking_number',
            ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo' => $user->profile_photo,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'banned_reason' => $user->banned_reason,
                'banned_at' => $user->banned_at?->toDateTimeString(),
                'created_at' => $user->created_at?->toDateTimeString(),
                'bookings_count' => $user->bookings_count,
                'addresses' => $user->addresses,
                'measurement' => $user->measurement,
                'provider' => $user->provider ? [
                    'id' => $user->provider->id,
                    'business_name' => $user->provider->business_name,
                    'description' => $user->provider->description,
                    'verification_status' => $user->provider->verification_status,
                    'is_suspended' => (bool) $user->provider->is_suspended,
                    'locations' => $user->provider->locations,
                    'items' => $user->provider->items,
                ] : null,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot modify your own account status.');
        }

        $isActive = ! $user->is_active;
        $user->update([
            'is_active' => $isActive,
            'banned_reason' => $isActive ? null : 'Disabled by admin toggle action.',
            'banned_at' => $isActive ? null : now(),
            'banned_by' => $isActive ? null : $request->user()->id,
        ]);

        return back()->with('success', $isActive ? 'User restored.' : 'User banned.');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot ban your own account.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user->update([
            'is_active' => false,
            'banned_reason' => $data['reason'],
            'banned_at' => now(),
            'banned_by' => $request->user()->id,
        ]);

        return back()->with('success', 'User has been banned.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->update([
            'is_active' => true,
            'banned_reason' => null,
            'banned_at' => null,
            'banned_by' => null,
        ]);

        return back()->with('success', 'User has been restored.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->hasActiveBookings($user)) {
            return back()->with('error', 'Cannot delete user with active bookings.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', Rule::in(['ban', 'restore', 'delete'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $users = User::whereIn('id', $data['user_ids'])
            ->where('id', '!=', $request->user()->id)
            ->get();

        DB::transaction(function () use ($users, $data, $request): void {
            foreach ($users as $user) {
                if ($data['action'] === 'ban') {
                    $user->update([
                        'is_active' => false,
                        'banned_reason' => $data['reason'] ?? 'Banned via bulk action.',
                        'banned_at' => now(),
                        'banned_by' => $request->user()->id,
                    ]);
                    continue;
                }

                if ($data['action'] === 'restore') {
                    $user->update([
                        'is_active' => true,
                        'banned_reason' => null,
                        'banned_at' => null,
                        'banned_by' => null,
                    ]);
                    continue;
                }

                if (! $this->hasActiveBookings($user)) {
                    $user->delete();
                }
            }
        });

        return back()->with('success', count($users).' user accounts updated.');
    }

    private function hasActiveBookings(User $user): bool
    {
        $activeStatuses = ['approved', 'ready_for_pickup', 'picked_up'];

        $hasRenterBookings = Booking::query()
            ->where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->exists();

        if ($hasRenterBookings) {
            return true;
        }

        $providerId = $user->provider?->id;
        if (! $providerId) {
            return false;
        }

        return Booking::query()
            ->where('provider_id', $providerId)
            ->whereIn('status', $activeStatuses)
            ->exists();
    }
}
