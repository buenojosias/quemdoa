<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'app.url' => 'https://quemleva.test',
    ]);
});

it('redirects guests to the Google authorization endpoint with csrf state', function () {
    $response = $this->get(route('auth.google.redirect'));

    $response
        ->assertRedirect()
        ->assertSessionHas('google_oauth_state');

    $location = $response->headers->get('Location');

    expect($location)->toBeString()
        ->and(Str::startsWith($location, 'https://accounts.google.com/o/oauth2/v2/auth?'))->toBeTrue();

    parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

    expect($query)
        ->toMatchArray([
            'client_id' => 'google-client-id',
            'redirect_uri' => route('auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'include_granted_scopes' => 'true',
            'state' => session('google_oauth_state'),
        ]);
});

it('creates and authenticates a verified Google account', function () {
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'google-access-token',
        ]),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-user-id',
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'email_verified' => true,
            'picture' => 'https://lh3.googleusercontent.com/avatar.jpg',
        ]),
    ]);

    $response = $this
        ->withSession(['google_oauth_state' => 'valid-state'])
        ->get(route('auth.google.callback', [
            'state' => 'valid-state',
            'code' => 'valid-code',
        ]));

    $response->assertRedirect(route('panel.dashboard', absolute: false));

    $user = User::query()->where('email', 'maria@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Maria Silva')
        ->and($user->google_id)->toBe('google-user-id')
        ->and($user->avatar)->toBe('https://lh3.googleusercontent.com/avatar.jpg')
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

it('links an existing email account to Google after callback', function () {
    $user = User::factory()->create([
        'email' => 'maria@example.com',
        'google_id' => null,
    ]);

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'google-access-token',
        ]),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-user-id',
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'email_verified' => true,
            'picture' => null,
        ]),
    ]);

    $response = $this
        ->withSession(['google_oauth_state' => 'valid-state'])
        ->get(route('auth.google.callback', [
            'state' => 'valid-state',
            'code' => 'valid-code',
        ]));

    $response->assertRedirect(route('panel.dashboard', absolute: false));

    expect($user->fresh()->google_id)->toBe('google-user-id');

    $this->assertAuthenticatedAs($user->fresh());
});

it('rejects callbacks with an invalid csrf state', function () {
    Http::fake();

    $response = $this
        ->withSession(['google_oauth_state' => 'valid-state'])
        ->get(route('auth.google.callback', [
            'state' => 'invalid-state',
            'code' => 'valid-code',
        ]));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('google');

    Http::assertNothingSent();
});

it('renders the Google redirect URI page', function () {
    $response = $this->get(route('auth.google.redirect-uri'));

    $response
        ->assertSuccessful()
        ->assertSee('URI de redirecionamento do Google')
        ->assertSee(route('auth.google.callback'), false);
});
