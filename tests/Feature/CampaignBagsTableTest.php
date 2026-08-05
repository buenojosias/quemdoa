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

function receivedBagForBagsTable(Campaign $campaign, string $participantName): Bag
{
    $bag = bagForBagsTable($campaign, $participantName, 'organizer');

    $bag->update([
        'received_at' => now(),
    ]);

    return $bag;
}

it('renders status filter options and maps bag status and confirmer labels', function () {
    $campaign = campaignForBagsTable();

    bagForBagsTable($campaign, 'Maria');
    bagForBagsTable($campaign, 'Joao', 'organizer');
    bagForBagsTable($campaign, 'Ana', 'participant');
    receivedBagForBagsTable($campaign, 'Clara');

    Livewire::test('panel.tables.campaign-bags', ['campaign' => $campaign])
        ->assertOk()
        ->assertSee('Todas')
        ->assertSee('Pendente')
        ->assertSee('Confirmada')
        ->assertSee('Recebida')
        ->assertSee('Maria')
        ->assertSee('Joao')
        ->assertSee('Ana')
        ->assertSee('Clara')
        ->assertSee('Mim')
        ->assertSee('Participante');
});

it('filters bags by pending, confirmed and received status', function () {
    $campaign = campaignForBagsTable();

    bagForBagsTable($campaign, 'Maria');
    bagForBagsTable($campaign, 'Joao', 'organizer');
    receivedBagForBagsTable($campaign, 'Clara');

    Livewire::test('panel.tables.campaign-bags', ['campaign' => $campaign])
        ->set('status', 'pending')
        ->assertSee('Maria')
        ->assertDontSee('Joao')
        ->assertDontSee('Clara')
        ->set('status', 'confirmed')
        ->assertSee('Joao')
        ->assertDontSee('Maria')
        ->assertDontSee('Clara')
        ->set('status', 'received')
        ->assertSee('Clara')
        ->assertDontSee('Maria')
        ->assertDontSee('Joao');
});
