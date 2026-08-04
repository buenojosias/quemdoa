<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('renders the login page with the QuemDoa layout', function () {
    $response = $this->get(route('login'));

    $response
        ->assertSuccessful()
        ->assertSee('Bem-vindo de volta!', false)
        ->assertSee('assets/images/logo.webp', false)
        ->assertSee('assets/images/illustration-login.png', false)
        ->assertSee('name="email"', false)
        ->assertSee('autocomplete="username"', false)
        ->assertSee('name="password"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee('id="remember_me"', false)
        ->assertSee('name="remember"', false)
        ->assertSee('href="'.route('auth.google.redirect').'"', false)
        ->assertSee('Entrar com Google');
});

it('remembers the user when remember me is checked', function () {
    $user = User::factory()->create();
    $recallerName = Auth::guard('web')->getRecallerName();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => '12345678',
        'remember' => 'on',
    ]);

    $response
        ->assertRedirect(route('panel.dashboard', absolute: false))
        ->assertCookie($recallerName);

    $this->assertAuthenticatedAs($user);
});
