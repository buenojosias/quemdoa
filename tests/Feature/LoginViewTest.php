<?php

it('renders the login page with the QuemLeva layout', function () {
    $response = $this->get(route('login'));

    $response
        ->assertSuccessful()
        ->assertSee('Bem-vindo de volta!', false)
        ->assertSee('assets/images/logomarca.png', false)
        ->assertSee('assets/images/illustration-login.png', false)
        ->assertSee('name="email"', false)
        ->assertSee('autocomplete="username"', false)
        ->assertSee('name="password"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee('type="button"', false)
        ->assertSee('Entrar com Google');
});
