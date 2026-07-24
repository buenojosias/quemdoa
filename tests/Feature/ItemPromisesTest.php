<?php

use App\Enums\CategoryEnum;
use App\Enums\PromiseItemStatusEnum;
use App\Enums\UnitEnum;
use App\Models\Campaign;
use App\Models\Item;
use App\Models\Promise;
use App\Models\PromiseItem;
use App\Models\User;
use Livewire\Livewire;

function campaignForItemPromises(): Campaign
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

function itemForItemPromises(Campaign $campaign, string $name = 'Arroz'): Item
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => $name,
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
    ]);
}

function promiseItemForItemPromises(Item $item, string $donorName, PromiseItemStatusEnum $status = PromiseItemStatusEnum::PENDING): PromiseItem
{
    $promise = Promise::create([
        'campaign_id' => $item->campaign_id,
        'donor_name' => $donorName,
    ]);

    return $promise->items()->create([
        'item_id' => $item->id,
        'promised_quantity' => 3,
        'status' => $status,
    ]);
}

it('opens the slide and lists promises for the selected item', function () {
    $campaign = campaignForItemPromises();
    $selectedItem = itemForItemPromises($campaign, 'Arroz');
    $otherItem = itemForItemPromises($campaign, 'Feijao');

    promiseItemForItemPromises($selectedItem, 'Maria');
    promiseItemForItemPromises($otherItem, 'Jose');

    Livewire::test('campaign.item-promises', ['campaignId' => $campaign->id])
        ->assertSet('slide', false)
        ->dispatch("open-item-promises.{$campaign->id}", item: $selectedItem->id)
        ->assertSet('slide', true)
        ->assertSee('Arroz')
        ->assertSee('Maria')
        ->assertDontSee('Jose')
        ->assertSee('Pendente')
        ->assertSee('Confirmar');
});

it('adds a promised item with a new promise', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);

    Livewire::test('promise.add-promise', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->assertSet('modal', true)
        ->assertSet('campaign_id', $campaign->id)
        ->assertSet('item_name', 'Arroz')
        ->set('donor_name', 'Maria')
        ->set('donor_whatsapp', '11 99999-9999')
        ->set('promised_quantity', 4)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('donor_name', '')
        ->assertSet('donor_whatsapp', '')
        ->assertSet('item_name', '')
        ->assertSet('promised_quantity', 0)
        ->assertSet('received', false)
        ->assertDispatched("promise-added.{$campaign->id}")
        ->assertDispatched("item-created.{$campaign->id}");

    $promise = Promise::query()->sole();
    $promiseItem = PromiseItem::query()->sole();

    expect($promise->campaign_id)->toBe($campaign->id)
        ->and($promise->donor_name)->toBe('Maria')
        ->and($promise->donor_whatsapp)->toBe('11 99999-9999')
        ->and($promise->confirmed_at)->not->toBeNull()
        ->and($promiseItem->promise_id)->toBe($promise->id)
        ->and($promiseItem->item_id)->toBe($item->id)
        ->and($promiseItem->promised_quantity)->toBe(4)
        ->and($promiseItem->status)->toBe(PromiseItemStatusEnum::PROMISED)
        ->and($item->refresh()->promised_quantity)->toBe(4)
        ->and($item->received_quantity)->toBe(0);
});

it('links an item to an existing promise by donor whatsapp', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);
    $promise = Promise::create([
        'campaign_id' => $campaign->id,
        'donor_name' => 'Maria Original',
        'donor_whatsapp' => '11 99999-9999',
    ]);

    Livewire::test('promise.add-promise', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->set('donor_name', 'Outro Nome')
        ->set('donor_whatsapp', '11 99999-9999')
        ->set('promised_quantity', 2)
        ->set('received', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched("promise-added.{$campaign->id}");

    expect(Promise::query()->count())->toBe(1)
        ->and($promise->refresh()->donor_name)->toBe('Maria Original')
        ->and($promise->confirmed_at)->not->toBeNull();

    $promiseItem = PromiseItem::query()->sole();

    expect($promiseItem->promise_id)->toBe($promise->id)
        ->and($promiseItem->status)->toBe(PromiseItemStatusEnum::RECEIVED)
        ->and($item->refresh()->promised_quantity)->toBe(2)
        ->and($item->received_quantity)->toBe(2);
});

it('refreshes item promises after a promise is added', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);

    $component = Livewire::test('campaign.item-promises', ['campaignId' => $campaign->id])
        ->dispatch("open-item-promises.{$campaign->id}", item: $item->id)
        ->assertSet('itemPromisedQuantity', 0)
        ->assertDontSee('Maria');

    promiseItemForItemPromises($item, 'Maria', PromiseItemStatusEnum::RECEIVED);
    $item->update([
        'promised_quantity' => 3,
        'received_quantity' => 3,
    ]);

    $component
        ->dispatch("promise-added.{$campaign->id}")
        ->assertSet('itemPromisedQuantity', 3)
        ->assertSet('itemReceivedQuantity', 3)
        ->assertSee('Maria');
});

it('clears add promise modal data when closed', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);

    Livewire::test('promise.add-promise', ['itemId' => $item->id])
        ->dispatch('open-add-modal')
        ->set('donor_name', 'Maria')
        ->set('donor_whatsapp', '11 99999-9999')
        ->set('promised_quantity', 4)
        ->set('received', true)
        ->dispatch('add-modal-closed')
        ->assertSet('modal', false)
        ->assertSet('campaign_id', null)
        ->assertSet('donor_name', '')
        ->assertSet('donor_whatsapp', '')
        ->assertSet('item_name', '')
        ->assertSet('promised_quantity', 0)
        ->assertSet('received', false);
});

it('confirms a pending promise and updates promised item totals', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);
    $promiseItem = promiseItemForItemPromises($item, 'Maria');

    Livewire::test('campaign.item-promises', ['campaignId' => $campaign->id])
        ->dispatch("open-item-promises.{$campaign->id}", item: $item->id)
        ->call('confirm', $promiseItem->id)
        ->assertDispatched("item-created.{$campaign->id}");

    expect($promiseItem->refresh()->status)->toBe(PromiseItemStatusEnum::PROMISED)
        ->and($promiseItem->promise->refresh()->confirmed_at)->not->toBeNull()
        ->and($item->refresh()->promised_quantity)->toBe(3)
        ->and($item->received_quantity)->toBe(0);
});

it('marks a promise as received and updates received item totals', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);
    $promiseItem = promiseItemForItemPromises($item, 'Maria', PromiseItemStatusEnum::PROMISED);

    Livewire::test('campaign.item-promises', ['campaignId' => $campaign->id])
        ->dispatch("open-item-promises.{$campaign->id}", item: $item->id)
        ->call('receive', $promiseItem->id)
        ->assertDispatched("item-created.{$campaign->id}");

    expect($promiseItem->refresh()->status)->toBe(PromiseItemStatusEnum::RECEIVED)
        ->and($item->refresh()->promised_quantity)->toBe(3)
        ->and($item->received_quantity)->toBe(3);
});

it('deletes a promise item and removes an empty promise', function () {
    $campaign = campaignForItemPromises();
    $item = itemForItemPromises($campaign);
    $promiseItem = promiseItemForItemPromises($item, 'Maria', PromiseItemStatusEnum::PROMISED);
    $promise = $promiseItem->promise;

    Livewire::test('campaign.item-promises', ['campaignId' => $campaign->id])
        ->dispatch("open-item-promises.{$campaign->id}", item: $item->id)
        ->call('delete', $promiseItem->id)
        ->assertDispatched("item-created.{$campaign->id}");

    $this->assertModelMissing($promiseItem);
    $this->assertModelMissing($promise);

    expect($item->refresh()->promised_quantity)->toBe(0)
        ->and($item->received_quantity)->toBe(0);
});
