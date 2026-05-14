<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoginRequest;
use App\Http\Requests\Web\RegisterRequest;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            return back()
                ->withErrors(['email' => 'The provided credentials are incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Your account is currently inactive.'])
                ->onlyInput('email');
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended($this->redirectPathForUser($user));
    }

    public function showRegister(Request $request): Response
    {
        $prefillRole = $request->query('role');
        $role = in_array($prefillRole, ['renter', 'provider'], true) ? $prefillRole : 'renter';

        return Inertia::render('Auth/Register', [
            'prefillRole' => $role,
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'email' => $data['email'],
                'password_hash' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'is_active' => true,
            ]);

            if ($user->role === 'provider') {
                Provider::create([
                    'user_id' => $user->id,
                    'business_name' => $data['business_name'],
                    'verification_status' => 'pending',
                ]);
            }

            Role::findOrCreate($user->role, 'sanctum');
            $user->syncRoles([$user->role]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->redirectPathForUser($user))
            ->with('success', 'Welcome to RentFit!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectPathForUser(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'provider' => route('provider.dashboard'),
            default => route('dashboard'),
        };
    }
}

