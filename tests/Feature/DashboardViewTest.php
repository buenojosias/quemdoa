<?php

use App\Models\User;

it('renders the dashboard with fictitious campaign data', function () {
    $user = User::factory()->create([
        'name' => 'João Silva',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertSee('Olá, João! Veja o resumo das suas campanhas.')
        ->assertSee('Adicione e confirme seu WhatsApp')
        ->assertSee('Campanhas ativas')
        ->assertSee('Itens no total')
        ->assertSee('Doações prometidas')
        ->assertSee('Doações recebidas')
        ->assertSee('Jantar da Padroeira 2025')
        ->assertSee('Campanha do Agasalho')
        ->assertSee('Próximos vencimentos')
        ->assertSee('Atividade recente');
});
