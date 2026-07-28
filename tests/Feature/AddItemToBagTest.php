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

function campaignForAddItemToBag(User $user): Campaign
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

function itemForAddItemToBag(
    Campaign $campaign,
    string $name,
    CategoryEnum $category = CategoryEnum::FOODS,
    int|float $requiredQuantity = 10,
    int|float $baggedQuantity = 0,
): CampaignItem {
    return $campaign->items()->create([
        'category' => $category->value,
        'name' => $name,
        'unit' => UnitEnum::KG->value,
        'required_quantity' => $requiredQuantity,
        'bagged_quantity' => $baggedQuantity,
    ]);
}

function bagForAddItemToBag(Campaign $campaign): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => 'ABC123',
        'participant_name' => 'Maria',
    ]);
}

it('mounts without loading campaign items', function () {
    $user = User::factory()->create();
    $campaign = campaignForAddItemToBag($user);
    $bag = bagForAddItemToBag($campaign);

    itemForAddItemToBag($campaign, 'Arroz');

    Livewire::actingAs($user)
        ->test('panel.bag.add-item', [
            'bagId' => $bag->id,
            'bagCode' => $bag->code,
        ])
        ->assertSet('itemsLoaded', false)
        ->assertSet('itemsByCategory', [])
        ->assertDontSee('Arroz');
});

it('loads available campaign items grouped by category when the modal opens', function () {
    $user = User::factory()->create();
    $campaign = campaignForAddItemToBag($user);
    $bag = bagForAddItemToBag($campaign);
    $availableFood = itemForAddItemToBag($campaign, 'Arroz', baggedQuantity: 2);
    itemForAddItemToBag($campaign, 'Suco', CategoryEnum::DRINKS);
    $alreadyInBag = itemForAddItemToBag($campaign, 'Feijao');

    $bag->items()->create([
        'campaign_item_id' => $alreadyInBag->id,
        'quantity' => 1,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    Livewire::actingAs($user)
        ->test('panel.bag.add-item', [
            'bagId' => $bag->id,
            'bagCode' => $bag->code,
        ])
        ->call('openModal')
        ->assertSet('itemsLoaded', true)
        ->assertSet('modal', true)
        ->assertSee('Comidas')
        ->assertSee('Bebidas')
        ->assertSee('Arroz')
        ->assertSee('Pendente: 8 kg')
        ->assertSee('Suco')
        ->assertDontSee('Feijao');

    expect($availableFood->refresh()->bagged_quantity)->toBe('2.0');
});

it('adds the selected item to the bag and updates item quantities', function () {
    $user = User::factory()->create();
    $campaign = campaignForAddItemToBag($user);
    $bag = bagForAddItemToBag($campaign);
    $item = itemForAddItemToBag($campaign, 'Arroz', baggedQuantity: 2);

    Livewire::actingAs($user)
        ->test('panel.bag.add-item', [
            'bagId' => $bag->id,
            'bagCode' => $bag->code,
        ])
        ->call('openModal')
        ->call('openAddModal', $item->id)
        ->assertSet('addModal', true)
        ->assertSet('selectedItemName', 'Arroz')
        ->assertSet('selectedItemPendingQuantity', 8.0)
        ->set('quantity', 3.5)
        ->set('received', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('addModal', false)
        ->assertSet('selectedItemId', null)
        ->assertSet('received', false)
        ->assertDispatched("bag-item-added.{$bag->id}")
        ->assertDispatched("item-created.{$campaign->id}")
        ->assertDontSee('Arroz');

    $bagItem = BagItem::query()->sole();

    expect($bagItem->bag_id)->toBe($bag->id)
        ->and($bagItem->campaign_item_id)->toBe($item->id)
        ->and($bagItem->quantity)->toBe('3.5')
        ->and($bagItem->status)->toBe(BagItemStatusEnum::RECEIVED)
        ->and($item->refresh()->bagged_quantity)->toBe('3.5')
        ->and($item->received_quantity)->toBe('3.5');
});

it('validates a positive numeric quantity before adding an item', function () {
    $user = User::factory()->create();
    $campaign = campaignForAddItemToBag($user);
    $bag = bagForAddItemToBag($campaign);
    $item = itemForAddItemToBag($campaign, 'Arroz');

    Livewire::actingAs($user)
        ->test('panel.bag.add-item', [
            'bagId' => $bag->id,
            'bagCode' => $bag->code,
        ])
        ->call('openModal')
        ->call('openAddModal', $item->id)
        ->set('quantity', 0)
        ->call('save')
        ->assertHasErrors(['quantity' => ['min']]);
});
