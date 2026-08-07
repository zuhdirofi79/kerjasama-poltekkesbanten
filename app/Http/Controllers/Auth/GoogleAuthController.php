<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        /** @var SocialiteUser $googleUser */
        $googleUser = Socialite::driver('google')->user();

        $emailVerified = (bool) ($googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false);

        if (! $emailVerified) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Email Google belum terverifikasi.']);
        }

        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Google tidak menyediakan email.']);
        }

        $user = User::query()
            ->where(function ($query) use ($googleUser, $email) {
                $query->where('google_id', $googleUser->getId())
                    ->orWhere('email', $email);
            })
            ->first();

        if ($user) {
            if (! $user->is_active) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Akun Anda tidak aktif.']);
            }

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            // Staff logging in via Google keep their existing role.
            // Unknown emails never become admin via Google (handled in create branch).
        } else {
            $user = User::query()->create([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'role' => UserRole::Visitor,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        if ($user->canAccessAdminPanel()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
