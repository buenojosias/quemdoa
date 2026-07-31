<?php

use App\Models\Campaign;
use App\Models\User;

it('renders the whatsapp confirmation bag finish message', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $this
        ->withSession([
            'bag_finish' => [
                'method' => 'whatsapp',
                'campaign_name' => 'Jantar da Comunidade',
                'participant_name' => 'Maria Silva',
                'bag_code' => 'QL-4827',
            ],
        ])
        ->get(route('public.campaigns.bag.finish', $campaign))
        ->assertSuccessful()
        ->assertSee('Sacola confirmada!')
        ->assertDontSee('Sacola cadastrada!')
        ->assertSee('Muito obrigado pela sua generosidade!')
        ->assertSee('Sua sacola foi confirmada com sucesso com o código')
        ->assertSee('QL-4827')
        ->assertSee('Que tal criar uma campanha?')
        ->assertSee('assets/images/illustration-finish.png', false)
        ->assertSee('assets/images/illustration-finish-cta.png', false)
        ->assertSee(route('welcome'), false)
        ->assertSee(route('register'), false);
});

it('renders the organizer confirmation bag finish message with clipboard text', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $this
        ->withSession([
            'bag_finish' => [
                'method' => 'organizer',
                'campaign_name' => 'Jantar da Comunidade',
                'participant_name' => 'Maria Silva',
                'bag_code' => 'QL-4827',
            ],
        ])
        ->get(route('public.campaigns.bag.finish', $campaign))
        ->assertSuccessful()
        ->assertSee('Sacola cadastrada!')
        ->assertDontSee('Sacola confirmada!')
        ->assertSee('Sua sacola foi cadastrada e está aguardando confirmação.')
        ->assertSee('Jantar da Comunidade')
        ->assertSee('Maria Silva')
        ->assertSee('QL-4827')
        ->assertSee('Pode confirmar para mim?');
});
