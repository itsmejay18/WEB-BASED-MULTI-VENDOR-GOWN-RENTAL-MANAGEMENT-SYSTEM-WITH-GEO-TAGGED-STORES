<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderVerificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'pending', 'verified', 'rejected'])],
            'suspended' => ['nullable', Rule::in(['all', 'yes', 'no'])],
        ]);

        if (empty($filters['status']) && $request->route('status') === 'pending') {
            $filters['status'] = 'pending';
        }

        $providers = Provider::query()
            ->with(['user:id,first_name,last_name,email', 'mainLocation:id,provider_id,city,state,country'])
            ->withCount('items')
            ->when(! empty($filters['status']) && $filters['status'] !== 'all', fn ($query) => $query->where('verification_status', $filters['status']))
            ->when(! empty($filters['suspended']) && $filters['suspended'] !== 'all', fn ($query) => $query->where('is_suspended', $filters['suspended'] === 'yes'))
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $term = $filters['search'];
                $query->where(function ($nestedQuery) use ($term): void {
                    $nestedQuery
                        ->where('business_name', 'like', "%{$term}%")
                        ->orWhereHas('user', function ($userQuery) use ($term): void {
                            $userQuery
                                ->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (Provider $provider): array {
                return [
                    'id' => $provider->id,
                    'business_name' => $provider->business_name,
                    'verification_status' => $provider->verification_status,
                    'is_suspended' => (bool) $provider->is_suspended,
                    'suspended_until' => $provider->suspended_until?->toDateTimeString(),
                    'items_count' => $provider->items_count,
                    'created_at' => $provider->created_at?->toDateTimeString(),
                    'owner' => [
                        'name' => trim(($provider->user?->first_name ?? '').' '.($provider->user?->last_name ?? '')),
                        'email' => $provider->user?->email,
                    ],
                    'main_location' => $provider->mainLocation ? [
                        'city' => $provider->mainLocation->city,
                        'state' => $provider->mainLocation->state,
                        'country' => $provider->mainLocation->country,
                    ] : null,
                ];
            });

        $stats = [
            'pending' => Provider::where('verification_status', 'pending')->count(),
            'verified' => Provider::where('verification_status', 'verified')->count(),
            'rejected' => Provider::where('verification_status', 'rejected')->count(),
            'suspended' => Provider::where('is_suspended', true)->count(),
            'total' => Provider::count(),
        ];

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers,
            'filters' => array_merge([
                'search' => '',
                'status' => 'all',
                'suspended' => 'all',
            ], $filters),
            'stats' => $stats,
        ]);
    }

    public function show(Provider $provider): Response
    {
        $provider->load([
            'user:id,first_name,last_name,email,phone,created_at',
            'locations.businessHours',
            'items' => fn ($query) => $query->withCount('bookings')->latest()->limit(12),
            'verificationLogs' => fn ($query) => $query->with('performedBy:id,first_name,last_name')->latest(),
        ]);

        return Inertia::render('Admin/Providers/Show', [
            'provider' => [
                'id' => $provider->id,
                'business_name' => $provider->business_name,
                'business_registration' => $provider->business_registration,
                'description' => $provider->description,
                'logo' => $provider->logo,
                'cover_photo' => $provider->cover_photo,
                'verification_status' => $provider->verification_status,
                'is_suspended' => (bool) $provider->is_suspended,
                'suspension_reason' => $provider->suspension_reason,
                'suspended_until' => $provider->suspended_until?->toDateTimeString(),
                'rejection_reason' => $provider->rejection_reason,
                'rating' => (float) $provider->rating,
                'total_reviews' => (int) $provider->total_reviews,
                'commission_rate' => (float) $provider->commission_rate,
                'created_at' => $provider->created_at?->toDateTimeString(),
                'user' => $provider->user,
                'locations' => $provider->locations,
                'items' => $provider->items,
                'verification_logs' => $provider->verificationLogs->map(fn ($log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'notes' => $log->notes,
                    'performed_by' => $log->performedBy
                        ? trim(($log->performedBy->first_name ?? '').' '.($log->performedBy->last_name ?? ''))
                        : null,
                    'created_at' => $log->created_at?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    public function verify(Request $request, Provider $provider): RedirectResponse
    {
        $provider->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        $provider->verificationLogs()->create([
            'action' => 'verified',
            'notes' => 'Provider verified by admin.',
            'performed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Provider verified.');
    }

    public function reject(Request $request, Provider $provider): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $provider->update([
            'verification_status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_reason' => $data['reason'],
        ]);

        $provider->verificationLogs()->create([
            'action' => 'rejected',
            'notes' => $data['reason'],
            'performed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Provider rejected.');
    }

    public function suspend(Request $request, Provider $provider): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'duration' => ['required', Rule::in(['3', '7', '30', 'permanent'])],
        ]);

        $suspendedUntil = $data['duration'] === 'permanent'
            ? null
            : now()->addDays((int) $data['duration']);

        $provider->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspended_until' => $suspendedUntil,
            'suspension_reason' => $data['reason'],
            'suspended_by' => $request->user()->id,
        ]);

        $provider->items()->update(['is_active' => false]);
        $provider->verificationLogs()->create([
            'action' => 'suspended',
            'notes' => sprintf('Duration: %s days. Reason: %s', $data['duration'], $data['reason']),
            'performed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Provider suspended.');
    }

    public function unsuspend(Request $request, Provider $provider): RedirectResponse
    {
        $provider->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspended_until' => null,
            'suspension_reason' => null,
            'suspended_by' => null,
        ]);

        if ($provider->verification_status === 'verified') {
            $provider->items()->update(['is_active' => true]);
        }

        $provider->verificationLogs()->create([
            'action' => 'unsuspended',
            'notes' => 'Suspension lifted by admin.',
            'performed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Provider suspension lifted.');
    }
}
