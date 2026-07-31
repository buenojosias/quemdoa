<?php

it('renders the register page with the QuemLeva layout', function () {
    $response = $this->get(route('register'));

    $response
        ->assertSuccessful()
        ->assertSee('Crie sua conta no QuemLeva')
        ->assertSee('assets/images/logomarca.png', false)
        ->assertSee('assets/images/illustration-register.png', false)
        ->assertSee('name="name"', false)
        ->assertSee('autocomplete="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('autocomplete="username"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('href="'.route('auth.google.redirect').'"', false)
        ->assertSee('Criar conta com Google')
        ->assertSee(route('login'), false);
});
