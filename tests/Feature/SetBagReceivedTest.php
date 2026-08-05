<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Livewire\Panel\Bag\Show;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

function campaignForSetBagReceived(User $user): Campaign
{
    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Alimentos',
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

function itemForSetBagReceived(Campaign $campaign, string $name): CampaignItem
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => $name,
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 0,
        'received_quantity' => 0,
    ]);
}

function bagForSetBagReceived(Campaign $campaign): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => 'Maria',
    ]);
}

it('opens the receive bag confirmation dialog with the selected bag data', function () {
    $user = User::factory()->create();
    $campaign = campaignForSetBagReceived($user);
    $bag = bagForSetBagReceived($campaign);
    $rice = itemForSetBagReceived($campaign, 'Arroz');

    $bag->items()->create([
        'campaign_item_id' => $rice->id,
        'quantity' => 2,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    Livewire::actingAs($user)
        ->test('panel.bag.set-bag-received')
        ->dispatch('open-set-bag-received', bag: $bag->id)
        ->assertSet('bagId', $bag->id)
        ->assertSet('campaignId', $campaign->id)
        ->assertSet('bagCode', $bag->code)
        ->assertSet('participantName', 'Maria')
        ->assertSet('itemsCount', 1);
});

it('marks every bag item as received and refreshes related components', function () {
    $user = User::factory()->create();
    $campaign = campaignForSetBagReceived($user);
    $bag = bagForSetBagReceived($campaign);
    $rice = itemForSetBagReceived($campaign, 'Arroz');
    $beans = itemForSetBagReceived($campaign, 'Feijao');

    $bag->items()->create([
        'campaign_item_id' => $rice->id,
        'quantity' => 2,
        'status' => BagItemStatusEnum::PENDING,
    ]);

    $bag->items()->create([
        'campaign_item_id' => $beans->id,
        'quantity' => 1.5,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    $otherBag = bagForSetBagReceived($campaign);

    $otherBag->items()->create([
        'campaign_item_id' => $rice->id,
        'quantity' => 3,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    Livewire::actingAs($user)
        ->test('panel.bag.set-bag-received')
        ->dispatch('open-set-bag-received', bag: $bag->id)
        ->call('save')
        ->assertSet('bagId', null)
        ->assertSet('campaignId', null)
        ->assertDispatchedTo('panel.tables.bag-items', "bag-item-received.{$bag->id}")
        ->assertDispatchedTo(Show::class, "bag-item-received.{$bag->id}")
        ->assertDispatched("campaign-bag-status-updated.{$campaign->id}", bag: $bag->id)
        ->assertDispatched("campaign-bag-item-received.{$campaign->id}", item: $rice->id)
        ->assertDispatched("campaign-bag-item-received.{$campaign->id}", item: $beans->id)
        ->assertDispatched("item-created.{$campaign->id}");

    expect($bag->refresh()->received_at)->not->toBeNull()
        ->and($bag->confirmed_at)->not->toBeNull()
        ->and($bag->confirmed_by)->toBe('organizer')
        ->and($bag->items()->where('status', BagItemStatusEnum::RECEIVED->value)->count())->toBe(2)
        ->and($rice->refresh()->bagged_quantity)->toBe('5.0')
        ->and($rice->received_quantity)->toBe('2.0')
        ->and($beans->refresh()->bagged_quantity)->toBe('1.5')
        ->and($beans->received_quantity)->toBe('1.5');
});
