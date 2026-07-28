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

function campaignForItemBags(): Campaign
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

function itemForItemBags(Campaign $campaign, string $name = 'Arroz'): CampaignItem
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => $name,
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
    ]);
}

function bagItemForItemBags(CampaignItem $item, string $participantName, BagItemStatusEnum $status = BagItemStatusEnum::PENDING): BagItem
{
    $bag = Bag::create([
        'campaign_id' => $item->campaign_id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => $participantName,
    ]);

    return $bag->items()->create([
        'campaign_item_id' => $item->id,
        'quantity' => 3,
        'status' => $status,
    ]);
}

it('opens the slide and lists bags for the selected item', function () {
    $campaign = campaignForItemBags();
    $selectedItem = itemForItemBags($campaign, 'Arroz');
    $otherItem = itemForItemBags($campaign, 'Feijao');

    bagItemForItemBags($selectedItem, 'Maria');
    bagItemForItemBags($otherItem, 'Jose');

    Livewire::test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->assertSet('slide', false)
        ->dispatch("open-item-bags.{$campaign->id}", item: $selectedItem->id)
        ->assertSet('slide', true)
        ->assertSee('Arroz')
        ->assertSee('Maria')
        ->assertDontSee('Jose')
        ->assertSee('Pendente')
        ->assertSee('Confirmar');
});

it('adds an item to a new bag', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);

    Livewire::test('panel.bag.add-bag', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->assertSet('modal', true)
        ->assertSet('campaign_id', $campaign->id)
        ->assertSet('item_name', 'Arroz')
        ->set('participant_name', 'Maria')
        ->set('participant_whatsapp', '11 99999-9999')
        ->set('quantity', 4)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('participant_name', '')
        ->assertSet('participant_whatsapp', '')
        ->assertSet('item_name', '')
        ->assertSet('quantity', 0)
        ->assertSet('received', false)
        ->assertDispatched("bag-added.{$campaign->id}")
        ->assertDispatched("item-created.{$campaign->id}");

    $bag = Bag::query()->sole();
    $bagItem = BagItem::query()->sole();

    expect($bag->campaign_id)->toBe($campaign->id)
        ->and($bag->participant_name)->toBe('Maria')
        ->and($bag->participant_whatsapp)->toBe('11 99999-9999')
        ->and($bag->confirmed_by)->toBe('organizer')
        ->and($bag->confirmed_at)->not->toBeNull()
        ->and($bagItem->bag_id)->toBe($bag->id)
        ->and($bagItem->campaign_item_id)->toBe($item->id)
        ->and($bagItem->quantity)->toBe('4.0')
        ->and($bagItem->status)->toBe(BagItemStatusEnum::CONFIRMED)
        ->and($item->refresh()->bagged_quantity)->toBe('4.0')
        ->and($item->received_quantity)->toBe('0.0');
});

it('links an item to an existing bag by participant whatsapp', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);
    $bag = Bag::create([
        'campaign_id' => $campaign->id,
        'code' => 'ABC123',
        'participant_name' => 'Maria Original',
        'participant_whatsapp' => '11 99999-9999',
    ]);

    Livewire::test('panel.bag.add-bag', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->set('participant_name', 'Outro Nome')
        ->set('participant_whatsapp', '11 99999-9999')
        ->set('quantity', 2)
        ->set('received', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched("bag-added.{$campaign->id}");

    expect(Bag::query()->count())->toBe(1)
        ->and($bag->refresh()->participant_name)->toBe('Maria Original')
        ->and($bag->confirmed_by)->toBe('organizer')
        ->and($bag->confirmed_at)->not->toBeNull();

    $bagItem = BagItem::query()->sole();

    expect($bagItem->bag_id)->toBe($bag->id)
        ->and($bagItem->status)->toBe(BagItemStatusEnum::RECEIVED)
        ->and($item->refresh()->bagged_quantity)->toBe('2.0')
        ->and($item->received_quantity)->toBe('2.0');
});

it('refreshes item bags after a bag is added', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);

    $component = Livewire::test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->dispatch("open-item-bags.{$campaign->id}", item: $item->id)
        ->assertSet('itemBaggedQuantity', 0)
        ->assertDontSee('Maria');

    bagItemForItemBags($item, 'Maria', BagItemStatusEnum::RECEIVED);
    $item->update([
        'bagged_quantity' => 3,
        'received_quantity' => 3,
    ]);

    $component
        ->dispatch("bag-added.{$campaign->id}")
        ->assertSet('itemBaggedQuantity', 3)
        ->assertSet('itemReceivedQuantity', 3)
        ->assertSee('Maria');
});

it('clears add bag modal data when closed', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);

    Livewire::test('panel.bag.add-bag', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->set('participant_name', 'Maria')
        ->set('participant_whatsapp', '11 99999-9999')
        ->set('quantity', 4)
        ->set('received', true)
        ->dispatch('add-modal-closed')
        ->assertSet('modal', false)
        ->assertSet('campaign_id', null)
        ->assertSet('participant_name', '')
        ->assertSet('participant_whatsapp', '')
        ->assertSet('item_name', '')
        ->assertSet('quantity', 0)
        ->assertSet('received', false);
});

it('confirms a pending bag item and keeps bagged item totals', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);
    $bagItem = bagItemForItemBags($item, 'Maria');
    $item->update(['bagged_quantity' => 3]);

    Livewire::test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->dispatch("open-item-bags.{$campaign->id}", item: $item->id)
        ->call('confirm', $bagItem->id)
        ->assertDispatched("item-created.{$campaign->id}");

    expect($bagItem->refresh()->status)->toBe(BagItemStatusEnum::CONFIRMED)
        ->and($bagItem->bag->refresh()->confirmed_by)->toBe('organizer')
        ->and($bagItem->bag->confirmed_at)->not->toBeNull()
        ->and($item->refresh()->bagged_quantity)->toBe('3.0')
        ->and($item->received_quantity)->toBe('0.0');
});

it('refreshes totals when a bag item is received by the shared component', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);
    $bagItem = bagItemForItemBags($item, 'Maria', BagItemStatusEnum::CONFIRMED);

    $component = Livewire::test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->dispatch("open-item-bags.{$campaign->id}", item: $item->id)
        ->assertSet('itemReceivedQuantity', 0);

    $bagItem->update(['status' => BagItemStatusEnum::RECEIVED]);

    $component
        ->dispatch("campaign-bag-item-received.{$campaign->id}", item: $item->id)
        ->assertSet('itemReceivedQuantity', 3)
        ->assertSet('formattedItemReceivedQuantity', '3')
        ->assertDispatched("item-created.{$campaign->id}");

    expect($bagItem->refresh()->status)->toBe(BagItemStatusEnum::RECEIVED)
        ->and($item->refresh()->bagged_quantity)->toBe('3.0')
        ->and($item->received_quantity)->toBe('3.0');
});

it('deletes a bag item and removes an empty bag', function () {
    $campaign = campaignForItemBags();
    $item = itemForItemBags($campaign);
    $bagItem = bagItemForItemBags($item, 'Maria', BagItemStatusEnum::CONFIRMED);
    $bag = $bagItem->bag;

    Livewire::test('panel.campaign.item-bags', ['campaignId' => $campaign->id])
        ->dispatch("open-item-bags.{$campaign->id}", item: $item->id)
        ->call('delete', $bagItem->id)
        ->assertDispatched("item-created.{$campaign->id}");

    $this->assertModelMissing($bagItem);
    expect(Bag::withTrashed()->find($bag->id)?->trashed())->toBeTrue();

    expect($item->refresh()->bagged_quantity)->toBe('0.0')
        ->and($item->received_quantity)->toBe('0.0');
});
