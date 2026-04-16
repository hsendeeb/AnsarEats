<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): SymfonyRedirectResponse
    {
        // Explicitly save the session to ensure the 'state' token is persisted
        // before the redirect occurs. This fixes the common issue where social 
        // login only works on the second attempt.
        request()->session()->save();

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $user = $this->resolveUser($provider, $socialUser);
        } catch (\Throwable $exception) {
            Log::warning('Social login failed.', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Unable to sign in with '.Str::title($provider).'. Please try again.',
                ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($user));
    }

    protected function resolveUser(string $provider, SocialiteUser $socialUser): User
    {
        $providerColumn = $this->providerColumn($provider);
        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();

        if (blank($providerId)) {
            throw new \RuntimeException('Provider user id missing.');
        }

        if (blank($email)) {
            throw new \RuntimeException('Provider email missing.');
        }

        $existingProviderUser = User::where($providerColumn, $providerId)->first();

        if ($existingProviderUser) {
            return $this->syncSocialFields($existingProviderUser, $providerColumn, $providerId, $socialUser);
        }

        $existingEmailUser = User::where('email', $email)->first();

        if ($existingEmailUser) {
            return $this->syncSocialFields($existingEmailUser, $providerColumn, $providerId, $socialUser);
        }

        $user = User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'New User',
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'role' => 'customer',
            'email_verified_at' => now(),
            $providerColumn => $providerId,
            'avatar' => $socialUser->getAvatar(),
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        } catch (\Throwable $exception) {
            Log::error('Failed to send welcome email after social registration: '.$exception->getMessage());
        }

        return $user;
    }

    protected function syncSocialFields(
        User $user,
        string $providerColumn,
        string $providerId,
        SocialiteUser $socialUser
    ): User {
        $updates = [
            $providerColumn => $user->{$providerColumn} ?: $providerId,
        ];

        if (blank($user->email_verified_at)) {
            $updates['email_verified_at'] = now();
        }

        if (blank($user->name)) {
            $updates['name'] = $socialUser->getName() ?: $socialUser->getNickname() ?: $user->name;
        }

        if (blank($user->avatar) && filled($socialUser->getAvatar())) {
            $updates['avatar'] = $socialUser->getAvatar();
        }

        $user->forceFill($updates)->save();

        return $user->refresh();
    }

    protected function providerColumn(string $provider): string
    {
        return match ($provider) {
            'google' => 'google_id',
            'facebook' => 'facebook_id',
            default => throw new \InvalidArgumentException('Unsupported provider.'),
        };
    }

    protected function redirectPathFor(User $user): string
    {
        if ($user->role === 'super_admin') {
            return route('filament.admin.pages.dashboard');
        }

        if ($user->role === 'owner') {
            return 'owner/dashboard';
        }

        return '/';
    }
}
