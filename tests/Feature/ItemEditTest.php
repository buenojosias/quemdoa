<?php

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Livewire\Panel\Item\Edit;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

function campaignForItemEdit(User $user): Campaign
{
    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Inverno',
        'description' => 'Arrecadacao de roupas e cobertores.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

function itemForItemEdit(Campaign $campaign): CampaignItem
{
    return $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'complement' => 'Pacote de 1kg',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'delivery_date' => today()->addDays(5)->toDateString(),
        'note' => 'Pacotes fechados.',
    ]);
}

it('renders the item edit component with a modal', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);

    actingAs($user);

    Livewire::test(Edit::class, ['campaignId' => $campaign->id])
        ->assertOk()
        ->assertViewIs('livewire.panel.item.edit')
        ->assertSee('Editar item')
        ->assertSet('modal', false);
});

it('loads the selected item data when opening the modal', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);
    $item = itemForItemEdit($campaign);

    actingAs($user);

    Livewire::test(Edit::class, ['campaignId' => $campaign->id])
        ->dispatch("open-item-edit.{$campaign->id}", item: $item->id)
        ->assertSet('modal', true)
        ->assertSet('itemId', $item->id)
        ->assertSet('category', CategoryEnum::FOODS->value)
        ->assertSet('name', 'Arroz')
        ->assertSet('complement', 'Pacote de 1kg')
        ->assertSet('unit', UnitEnum::KG->value)
        ->assertSet('required_quantity', 10.0)
        ->assertSet('delivery_date', today()->addDays(5)->toDateString())
        ->assertSet('note', 'Pacotes fechados.');
});

it('updates the selected item and refreshes the table', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);
    $item = itemForItemEdit($campaign);

    actingAs($user);

    Livewire::test(Edit::class, ['campaignId' => $campaign->id])
        ->dispatch("open-item-edit.{$campaign->id}", item: $item->id)
        ->set([
            'category' => CategoryEnum::HYGIENE->value,
            'name' => 'Sabonete',
            'complement' => 'Pacote com 6',
            'unit' => UnitEnum::PACK->value,
            'required_quantity' => 15,
            'delivery_date' => today()->addDays(7)->toDateString(),
            'note' => 'Preferir neutro.',
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('itemId', null)
        ->assertDispatched("item-created.{$campaign->id}")
        ->assertDispatched("item-updated.{$campaign->id}");

    assertDatabaseHas('campaign_items', [
        'id' => $item->id,
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::HYGIENE->value,
        'name' => 'Sabonete',
        'complement' => 'Pacote com 6',
        'unit' => UnitEnum::PACK->value,
        'required_quantity' => 15,
        'delivery_date' => today()->addDays(7)->startOfDay()->toDateTimeString(),
        'note' => 'Preferir neutro.',
    ]);
});

it('requires item fields while editing', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);
    $item = itemForItemEdit($campaign);

    actingAs($user);

    Livewire::test(Edit::class, ['campaignId' => $campaign->id])
        ->dispatch("open-item-edit.{$campaign->id}", item: $item->id)
        ->set('category', '')
        ->set('name', '')
        ->set('unit', '')
        ->set('required_quantity', null)
        ->call('save')
        ->assertHasErrors([
            'category' => 'required',
            'name' => 'required',
            'unit' => 'required',
            'required_quantity' => 'required',
        ]);
});

it('requires edited item delivery date between tomorrow and the day before the campaign delivery deadline', function (string $deliveryDate, string $rule) {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);
    $item = itemForItemEdit($campaign);

    actingAs($user);

    Livewire::test(Edit::class, ['campaignId' => $campaign->id])
        ->dispatch("open-item-edit.{$campaign->id}", item: $item->id)
        ->set('delivery_date', $deliveryDate)
        ->call('save')
        ->assertHasErrors(['delivery_date' => [$rule]]);
})->with([
    'today' => fn () => [today()->toDateString(), 'after'],
    'campaign delivery deadline' => fn () => [today()->addDays(20)->toDateString(), 'before'],
]);

it('mounts the edit component inside the campaign items table', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemEdit($user);
    itemForItemEdit($campaign);

    actingAs($user);

    Livewire::test('panel.tables.campaign-items', ['campaign' => $campaign])
        ->assertSee('Arroz')
        ->assertSeeLivewire(Edit::class);
});
