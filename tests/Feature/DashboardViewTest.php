<?php

use App\Models\Campaign;
use App\Models\User;

it('renders the dashboard with real campaign data', function () {
    $user = User::factory()->create([
        'name' => 'João Silva',
    ]);

    Campaign::query()->create([
        'user_id' => $user->id,
        'name' => 'Campanha Real do Dashboard',
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('panel.dashboard'));

    $response
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertSee('Olá, João! Veja o resumo das suas campanhas.')
        ->assertSee('Adicione e confirme seu WhatsApp')
        ->assertSee('Campanhas ativas')
        ->assertSee('Sacolas cadastradas')
        ->assertSee('Sacolas a confirmar')
        ->assertSee('Sacolas recebidas')
        ->assertSee('Campanha Real do Dashboard')
        ->assertSee('Próximos vencimentos')
        ->assertSee('Atividade recente');
});
