<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GoogleAuthController
{
    public function redirect(Request $request): RedirectResponse
    {
        $this->ensureGoogleIsConfigured();

        $state = Str::random(40);

        $request->session()->put('google_oauth_state', $state);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'include_granted_scopes' => 'true',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->ensureGoogleIsConfigured();

        if ($request->string('state')->toString() !== $request->session()->pull('google_oauth_state')) {
            return $this->redirectToLoginWithError('Não foi possível validar o retorno do Google. Tente novamente.');
        }

        if ($request->filled('error')) {
            return $this->redirectToLoginWithError('O acesso com Google foi cancelado ou negado.');
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            return $this->redirectToLoginWithError('O Google não retornou um código de autenticação válido.');
        }

        try {
            $googleProfile = $this->fetchGoogleProfile($code);
            $user = $this->findOrCreateUser($googleProfile);
        } catch (RequestException) {
            return $this->redirectToLoginWithError('Não foi possível se comunicar com o Google. Tente novamente.');
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard', absolute: false));
    }

    public function redirectUri(): View
    {
        return view('auth.google-redirect-uri', [
            'redirectUri' => route('auth.google.callback'),
        ]);
    }

    /**
     * @return array{sub: string, email: string, email_verified?: bool, name?: string, picture?: string}
     */
    private function fetchGoogleProfile(string $code): array
    {
        $token = Http::asForm()
            ->acceptJson()
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('auth.google.callback'),
                'grant_type' => 'authorization_code',
            ])
            ->throw()
            ->json();

        $accessToken = data_get($token, 'access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw ValidationException::withMessages([
                'google' => 'O Google não retornou um token de acesso válido.',
            ]);
        }

        /** @var array{sub: string, email: string, email_verified?: bool, name?: string, picture?: string} $profile */
        $profile = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://openidconnect.googleapis.com/v1/userinfo')
            ->throw()
            ->json();

        return $profile;
    }

    /**
     * @param  array{sub?: string, email?: string, email_verified?: bool, name?: string, picture?: string}  $googleProfile
     */
    private function findOrCreateUser(array $googleProfile): User
    {
        $googleId = data_get($googleProfile, 'sub');
        $email = data_get($googleProfile, 'email');

        if (! is_string($googleId) || $googleId === '' || ! is_string($email) || $email === '') {
            throw ValidationException::withMessages([
                'google' => 'O Google não retornou os dados necessários para autenticação.',
            ]);
        }

        if (data_get($googleProfile, 'email_verified') !== true) {
            throw ValidationException::withMessages([
                'google' => 'Use uma conta Google com e-mail verificado.',
            ]);
        }

        $user = User::query()->where('google_id', $googleId)->first()
            ?? User::query()->where('email', $email)->first();

        if ($user !== null && $user->google_id !== null && $user->google_id !== $googleId) {
            throw ValidationException::withMessages([
                'google' => 'Este e-mail já está vinculado a outra conta Google.',
            ]);
        }

        $user ??= new User();

        $googleName = data_get($googleProfile, 'name');
        $googleAvatar = data_get($googleProfile, 'picture');

        $user->forceFill([
            'name' => is_string($googleName) && $googleName !== '' ? $googleName : Str::before($email, '@'),
            'email' => $email,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'google_id' => $googleId,
            'avatar' => is_string($googleAvatar) ? $googleAvatar : null,
        ])->save();

        return $user;
    }

    private function ensureGoogleIsConfigured(): void
    {
        if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))) {
            return;
        }

        throw ValidationException::withMessages([
            'google' => 'Configure GOOGLE_CLIENT_ID e GOOGLE_CLIENT_SECRET para ativar o login com Google.',
        ]);
    }

    private function redirectToLoginWithError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'google' => $message,
        ]);
    }
}
