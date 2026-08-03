<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

function campaignForChangeItemQuantity(User $user): Campaign
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

function itemForChangeItemQuantity(Campaign $campaign): CampaignItem
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 5,
        'received_quantity' => 2,
    ]);
}

function bagItemForChangeItemQuantity(CampaignItem $item, BagItemStatusEnum $status, int|float $quantity = 3): BagItem
{
    $bag = Bag::create([
        'campaign_id' => $item->campaign_id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => 'Maria',
    ]);

    return $bag->items()->create([
        'campaign_item_id' => $item->id,
        'quantity' => $quantity,
        'status' => $status,
    ]);
}

it('opens with the selected bag item quantity', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $bagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED, 3.5);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->assertSet('modal', false)
        ->dispatch('open-change-item-quantity', bagItem: $bagItem->id)
        ->assertSet('modal', true)
        ->assertSet('bagItemId', $bagItem->id)
        ->assertSet('itemName', 'Arroz')
        ->assertSet('currentFormattedQuantity', '3,5')
        ->assertSet('quantity', 3.5)
        ->assertSee('Arroz')
        ->assertSee('Quantidade atual: 3,5');
});

it('changes a confirmed item quantity and refreshes parent components', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $changedBagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED, 3);
    bagItemForChangeItemQuantity($item, BagItemStatusEnum::RECEIVED, 2);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->dispatch('open-change-item-quantity', bagItem: $changedBagItem->id)
        ->dispatch('change-item-quantity-save', quantity: 4.5)
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('bagItemId', null)
        ->assertDispatched("bag-item-quantity-updated.{$changedBagItem->bag_id}")
        ->assertDispatched("campaign-bag-item-quantity-updated.{$campaign->id}")
        ->assertDispatched("item-created.{$campaign->id}");

    expect($changedBagItem->refresh()->quantity)->toBe('4.5')
        ->and($item->refresh()->bagged_quantity)->toBe('6.5')
        ->and($item->received_quantity)->toBe('2.0');
});

it('changes a received item quantity and refreshes received totals', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED, 3);
    $changedBagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::RECEIVED, 2);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->dispatch('open-change-item-quantity', bagItem: $changedBagItem->id)
        ->set('quantity', 1.5)
        ->dispatch('change-item-quantity-save')
        ->assertHasNoErrors();

    expect($changedBagItem->refresh()->quantity)->toBe('1.5')
        ->and($item->refresh()->bagged_quantity)->toBe('4.5')
        ->and($item->received_quantity)->toBe('1.5');
});

it('does not include pending item quantity in bagged totals after changing quantity', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $changedBagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::PENDING, 3);
    bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED, 2);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->dispatch('open-change-item-quantity', bagItem: $changedBagItem->id)
        ->dispatch('change-item-quantity-save', quantity: 5)
        ->assertHasNoErrors()
        ->assertDispatched(
            "campaign-bag-item-quantity-updated.{$campaign->id}",
            item: $item->id,
            status: BagItemStatusEnum::PENDING->value,
        );

    expect($changedBagItem->refresh()->quantity)->toBe('5.0')
        ->and($item->refresh()->bagged_quantity)->toBe('2.0')
        ->and($item->received_quantity)->toBe('0.0');
});

it('does not let the item bags listener recalculate bagged totals for pending quantity changes', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $pendingBagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::PENDING, 3);
    bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED, 2);

    $item->update([
        'bagged_quantity' => 2,
        'received_quantity' => 0,
    ]);

    $component = Livewire::actingAs($user)
        ->test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->dispatch("open-item-bags.{$campaign->id}", item: $item->id)
        ->assertSet('itemBaggedQuantity', 2.0)
        ->assertSet('itemReceivedQuantity', 0.0);

    $pendingBagItem->update(['quantity' => 5]);

    $component
        ->dispatch(
            "campaign-bag-item-quantity-updated.{$campaign->id}",
            item: $item->id,
            status: BagItemStatusEnum::PENDING->value,
        )
        ->assertSet('itemBaggedQuantity', 2.0)
        ->assertSet('itemReceivedQuantity', 0.0);

    expect($item->refresh()->bagged_quantity)->toBe('2.0')
        ->and($item->received_quantity)->toBe('0.0');
});

it('validates the new quantity', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $bagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->dispatch('open-change-item-quantity', bagItem: $bagItem->id)
        ->set('quantity', 0)
        ->dispatch('change-item-quantity-save')
        ->assertHasErrors(['quantity' => ['min']]);
});

it('resets when the modal is closed', function () {
    $user = User::factory()->create();
    $campaign = campaignForChangeItemQuantity($user);
    $item = itemForChangeItemQuantity($campaign);
    $bagItem = bagItemForChangeItemQuantity($item, BagItemStatusEnum::CONFIRMED);

    Livewire::actingAs($user)
        ->test('panel.bag.change-item-quantity')
        ->dispatch('open-change-item-quantity', bagItem: $bagItem->id)
        ->assertSet('modal', true)
        ->dispatch('change-item-quantity-modal-closed')
        ->assertSet('modal', false)
        ->assertSet('bagItemId', null)
        ->assertSet('quantity', 0.0);
});
