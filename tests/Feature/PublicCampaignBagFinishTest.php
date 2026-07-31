<?php

use App\Models\Campaign;
use App\Models\User;

it('renders the public bag finish confirmation page', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $this->get(route('public.campaigns.bag.finish', $campaign))
        ->assertSuccessful()
        ->assertSee('Sacola confirmada!')
        ->assertSee('Muito obrigado pela sua generosidade!')
        ->assertSee('Acompanhe o impacto da sua doação!')
        ->assertSee('Que tal criar uma campanha?')
        ->assertSee('assets/images/illustration-finish.png', false)
        ->assertSee('assets/images/illustration-finish-cta.png', false)
        ->assertSee(route('public.campaigns.show', $campaign), false)
        ->assertSee(route('register'), false);
});
