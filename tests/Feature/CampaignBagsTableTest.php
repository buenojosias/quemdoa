<?php

use App\Models\Bag;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

function campaignForBagsTable(): Campaign
{
    $user = User::factory()->create();

    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Alimentos',
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

function bagForBagsTable(Campaign $campaign, string $participantName, ?string $confirmedBy = null): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => $participantName,
        'participant_whatsapp' => fake()->phoneNumber(),
        'confirmed_by' => $confirmedBy,
        'confirmed_at' => $confirmedBy ? now() : null,
    ]);
}

it('renders status filter options and maps bag status and confirmer labels', function () {
    $campaign = campaignForBagsTable();

    bagForBagsTable($campaign, 'Maria');
    bagForBagsTable($campaign, 'Joao', 'organizer');
    bagForBagsTable($campaign, 'Ana', 'participant');

    Livewire::test('panel.campaign.bags-table', ['campaignId' => $campaign->id])
        ->assertOk()
        ->assertSee('Todos')
        ->assertSee('Pendente')
        ->assertSee('Confirmado')
        ->assertSee('Maria')
        ->assertSee('Joao')
        ->assertSee('Ana')
        ->assertSee('Mim')
        ->assertSee('Participante');
});

it('filters bags by pending and confirmed status', function () {
    $campaign = campaignForBagsTable();

    bagForBagsTable($campaign, 'Maria');
    bagForBagsTable($campaign, 'Joao', 'organizer');

    Livewire::test('panel.campaign.bags-table', ['campaignId' => $campaign->id])
        ->set('status', 'pending')
        ->assertSee('Maria')
        ->assertDontSee('Joao')
        ->set('status', 'confirmed')
        ->assertSee('Joao')
        ->assertDontSee('Maria');
});
