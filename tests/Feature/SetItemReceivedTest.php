<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Livewire\Panel\Bag\Show;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

function receivedTestCampaign(User $user): Campaign
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

function receivedTestItem(Campaign $campaign): CampaignItem
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 3,
        'received_quantity' => 0,
    ]);
}

function receivedTestBagItem(CampaignItem $item, BagItemStatusEnum $status = BagItemStatusEnum::CONFIRMED): BagItem
{
    $bag = Bag::create([
        'campaign_id' => $item->campaign_id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => 'Maria',
    ]);

    return $bag->items()->create([
        'campaign_item_id' => $item->id,
        'quantity' => 3,
        'status' => $status,
    ]);
}

it('opens without initial data and loads the selected bag item from an event', function () {
    $user = User::factory()->create();
    $campaign = receivedTestCampaign($user);
    $item = receivedTestItem($campaign);
    $bagItem = receivedTestBagItem($item);

    Livewire::actingAs($user)
        ->test('panel.bag.set-item-received')
        ->assertSet('modal', false)
        ->assertSet('bagItemId', null)
        ->dispatch('open-set-item-received', bagItem: $bagItem->id)
        ->assertSet('modal', true)
        ->assertSet('bagItemId', $bagItem->id)
        ->assertSet('itemName', 'Arroz')
        ->assertSet('formattedBagItemQuantity', '3')
        ->assertSet('receivedQuantity', 3.0)
        ->assertSeeHtml('value="3"')
        ->assertSee('Arroz');
});

it('marks a bag item as received and refreshes parent components', function () {
    $user = User::factory()->create();
    $campaign = receivedTestCampaign($user);
    $item = receivedTestItem($campaign);
    $bagItem = receivedTestBagItem($item);

    Livewire::actingAs($user)
        ->test('panel.bag.set-item-received')
        ->dispatch('open-set-item-received', bagItem: $bagItem->id)
        ->dispatch('set-item-received-save', receivedQuantity: 2.5)
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('bagItemId', null)
        ->assertDispatched("bag-item-received.{$bagItem->bag_id}")
        ->assertDispatchedTo('panel.tables.bag-items', "bag-item-received.{$bagItem->bag_id}")
        ->assertDispatchedTo(Show::class, "bag-item-received.{$bagItem->bag_id}")
        ->assertDispatched("campaign-bag-item-received.{$campaign->id}")
        ->assertDispatched("item-created.{$campaign->id}");

    expect($bagItem->refresh()->quantity)->toBe('2.5')
        ->and($bagItem->status)->toBe(BagItemStatusEnum::RECEIVED)
        ->and($bagItem->bag->refresh()->confirmed_by)->toBe('organizer')
        ->and($bagItem->bag->confirmed_at)->not->toBeNull()
        ->and($bagItem->bag->received_at)->not->toBeNull()
        ->and($item->refresh()->bagged_quantity)->toBe('2.5')
        ->and($item->received_quantity)->toBe('2.5');
});

it('does not mark the bag as received while another bag item is not received', function () {
    $user = User::factory()->create();
    $campaign = receivedTestCampaign($user);
    $rice = receivedTestItem($campaign);
    $beans = $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Feijao',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 3,
        'received_quantity' => 0,
    ]);
    $bagItem = receivedTestBagItem($rice);

    $bagItem->bag->items()->create([
        'campaign_item_id' => $beans->id,
        'quantity' => 3,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    Livewire::actingAs($user)
        ->test('panel.bag.set-item-received')
        ->dispatch('open-set-item-received', bagItem: $bagItem->id)
        ->dispatch('set-item-received-save', receivedQuantity: 2.5)
        ->assertHasNoErrors();

    expect($bagItem->bag->refresh()->received_at)->toBeNull();
});

it('validates quantity before receiving an item', function () {
    $user = User::factory()->create();
    $campaign = receivedTestCampaign($user);
    $item = receivedTestItem($campaign);
    $bagItem = receivedTestBagItem($item);

    Livewire::actingAs($user)
        ->test('panel.bag.set-item-received')
        ->dispatch('open-set-item-received', bagItem: $bagItem->id)
        ->set('receivedQuantity', 0)
        ->dispatch('set-item-received-save')
        ->assertHasErrors(['receivedQuantity' => ['min']]);
});

it('resets the form when the modal is closed', function () {
    $user = User::factory()->create();
    $campaign = receivedTestCampaign($user);
    $item = receivedTestItem($campaign);
    $bagItem = receivedTestBagItem($item);

    Livewire::actingAs($user)
        ->test('panel.bag.set-item-received')
        ->dispatch('open-set-item-received', bagItem: $bagItem->id)
        ->assertSet('modal', true)
        ->dispatch('set-item-received-modal-closed')
        ->assertSet('modal', false)
        ->assertSet('bagItemId', null)
        ->assertSet('receivedQuantity', 0.0);
});
