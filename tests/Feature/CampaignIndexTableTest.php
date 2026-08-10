<?php

use App\Livewire\Panel\Campaign\Index;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders campaigns in a table with the requested columns', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->for($user)->create([
        'name' => 'Campanha de Inverno',
        'confirmation_deadline' => '2026-08-10',
        'delivery_deadline' => '2026-08-20',
        'is_active' => true,
    ]);

    CampaignItem::factory()->for($campaign, 'campaign')->count(2)->create();

    Bag::create([
        'campaign_id' => $campaign->id,
        'code' => 'CAMP-001',
        'participant_name' => 'Maria Silva',
        'participant_whatsapp' => '11999999999',
        'confirmation_code' => '123456',
    ]);

    actingAs($user);

    Livewire::test(Index::class)
        ->assertOk()
        ->assertViewIs('livewire.panel.campaign.index')
        ->assertSee('Nome')
        ->assertSee('Prazo de confirmação')
        ->assertSee('Prazo de entrega')
        ->assertSee('Itens')
        ->assertSee('Sacolas')
        ->assertSee('Status')
        ->assertSee('Campanha de Inverno')
        ->assertSee('10/08/2026')
        ->assertSee('20/08/2026')
        ->assertSee('Ativa');
});

it('renders the empty campaign state', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(Index::class)
        ->assertOk()
        ->assertSee('Você ainda não criou nenhuma campanha')
        ->assertSee('Crie sua primeira campanha e comece a organizar doações de forma simples, prática e transparente.')
        ->assertSee('assets/images/empty-illustration.webp')
        ->assertSee('open-campaign-create')
        ->assertSee('Criar minha primeira campanha')
        ->assertDontSee('Itens por página');
});

it('filters campaigns by status and controls the page quantity', function () {
    $user = User::factory()->create();

    Campaign::factory()->for($user)->create([
        'name' => 'Campanha Ativa',
        'is_active' => true,
    ]);

    Campaign::factory()->for($user)->create([
        'name' => 'Campanha Inativa',
        'is_active' => false,
    ]);

    actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('quantity', 10)
        ->assertSet('status', '')
        ->assertSee('Itens por página')
        ->assertSee('Status')
        ->set('quantity', 5)
        ->assertSet('quantity', 5)
        ->set('status', 'active')
        ->assertSee('Campanha Ativa')
        ->assertDontSee('Campanha Inativa')
        ->set('status', 'inactive')
        ->assertSee('Campanha Inativa')
        ->assertDontSee('Campanha Ativa');
});
