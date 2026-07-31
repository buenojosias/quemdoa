<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

it('renders campaign items grouped by category with item details', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'complement' => 'Pacote 5kg',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
        'delivery_date' => today()->addDays(10)->toDateString(),
        'note' => 'Preferência por arroz tipo 1.',
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Carne bovina',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 12,
        'bagged_quantity' => 0,
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::DRINKS->value,
        'name' => 'Refrigerante',
        'unit' => UnitEnum::L->value,
        'required_quantity' => 10,
        'bagged_quantity' => 14,
    ]);

    $this->get(route('public.campaigns.show', $campaign))
        ->assertSuccessful()
        ->assertSee('Jantar da Comunidade')
        ->assertSeeInOrder(['Comidas', 'Arroz', 'Carne bovina', 'Bebidas', 'Refrigerante'])
        ->assertSee('assets/images/category-illustrations/foods.png', false)
        ->assertSee('assets/images/category-illustrations/drinks.png', false)
        ->assertSee('2 itens')
        ->assertSee('Pacote 5kg')
        ->assertSee('8 un pendente')
        ->assertSee('20 un.')
        ->assertSee('12 un. (60%)')
        ->assertSee('Preferência por arroz tipo 1.')
        ->assertSee('Na sacola')
        ->assertSee('0 l pendente');
});

it('updates the public item button when items are added and removed from the temporary bag', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    Livewire::test(\App\Livewire\Public\Campaign\Show::class, ['campaign' => $campaign])
        ->assertSee('Vou levar')
        ->dispatch("public-campaign-item-added.{$campaign->id}", item: $item->id, bagItem: [
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 1,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ])
        ->assertSee('Adicionado à sacola')
        ->assertSet('bagItems.0.name', 'Arroz')
        ->assertSet('bagSlide', true)
        ->dispatch("public-campaign-bag-item-quantity-updated.{$campaign->id}", item: $item->id, quantity: 2.5)
        ->assertSet('bagItems.0.quantity', 2.5)
        ->assertSet('bagItems.0.formattedQuantity', '2,5')
        ->dispatch("public-campaign-item-removed.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems', [])
        ->assertSee('Vou levar');
});

it('opens the public item add modal and dispatches the temporary bag item', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'complement' => 'Pacote 5kg',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
        'note' => 'Preferência por arroz tipo 1.',
    ]);

    Livewire::test('public.campaign.item-add', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-item-add.{$campaign->id}", item: $item->id)
        ->assertSet('modal', true)
        ->assertSet('itemName', 'Arroz')
        ->assertSet('pendingBaggedQuantity', 8.0)
        ->assertSee('Pacote 5kg')
        ->set('quantity', 2.5)
        ->dispatch('public-campaign-item-add-save', quantity: 2.5)
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('itemId', null)
        ->assertDispatched("public-campaign-item-added.{$campaign->id}")
        ->assertDispatched("open-public-campaign-bag.{$campaign->id}");
});

it('accepts the full decimal pending quantity in the public item add modal', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 5,
        'bagged_quantity' => 2.5,
    ]);

    Livewire::test('public.campaign.item-add', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-item-add.{$campaign->id}", item: $item->id)
        ->assertSet('pendingBaggedQuantity', 2.5)
        ->assertSet('quantity', 1.0)
        ->dispatch('public-campaign-item-add-save', quantity: 2.5)
        ->assertHasNoErrors()
        ->assertDispatched("public-campaign-item-added.{$campaign->id}");
});

it('normalizes public item add quantity to the decimal pending maximum while editing', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 5,
        'bagged_quantity' => 2.5,
    ]);

    Livewire::test('public.campaign.item-add', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-item-add.{$campaign->id}", item: $item->id)
        ->dispatch('public-campaign-item-add-quantity-updated', quantity: 2.3)
        ->assertSet('quantity', 2.3)
        ->dispatch('public-campaign-item-add-quantity-updated', quantity: 2.6)
        ->assertSet('quantity', 2.5);
});

it('does not allow public item add quantity above the decimal pending amount', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 5,
        'bagged_quantity' => 2.5,
    ]);

    Livewire::test('public.campaign.item-add', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-item-add.{$campaign->id}", item: $item->id)
        ->dispatch('public-campaign-item-add-save', quantity: 2.6)
        ->assertHasErrors(['quantity' => ['max']]);
});

it('validates public item add quantity as required with a minimum value', function (mixed $quantity, string $rule) {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    Livewire::test('public.campaign.item-add', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-item-add.{$campaign->id}", item: $item->id)
        ->set('quantity', $quantity)
        ->dispatch('public-campaign-item-add-save', quantity: $quantity)
        ->assertHasErrors(['quantity' => [$rule]]);
})->with([
    'required' => ['', 'required'],
    'minimum' => [0, 'min'],
]);

it('manages public temporary bag items and emits item removal events', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    Livewire::test('public.campaign.bag', ['campaignId' => $campaign->id])
        ->assertSet('bagItems', [])
        ->dispatch("public-campaign-item-added.{$campaign->id}", item: $item->id, bagItem: [
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 1,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ])
        ->assertSet('bagItems.0.name', 'Arroz')
        ->assertSet('bagItems.0.quantity', 1.0)
        ->dispatch("public-campaign-bag-increment.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems.0.quantity', 1.5)
        ->assertDispatched("public-campaign-bag-item-quantity-updated.{$campaign->id}")
        ->dispatch("public-campaign-bag-decrement.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems.0.quantity', 1.0)
        ->assertDispatched("public-campaign-bag-item-quantity-updated.{$campaign->id}")
        ->dispatch("public-campaign-bag-remove.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems', [])
        ->assertDispatched("public-campaign-item-removed.{$campaign->id}");
});

it('opens the public confirm bag modal from the temporary bag', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    Livewire::test('public.campaign.bag', ['campaignId' => $campaign->id])
        ->dispatch("public-campaign-item-added.{$campaign->id}", item: $item->id, bagItem: [
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 1,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ])
        ->dispatch("public-campaign-bag-finish.{$campaign->id}")
        ->assertSet('slide', false)
        ->assertDispatched("open-public-campaign-confirm-bag.{$campaign->id}");
});

it('creates a pending public bag for organizer confirmation and flashes the bag code', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 8,
        'bagged_quantity' => 0,
    ]);

    Livewire::test('public.campaign.confirm-bag', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-confirm-bag.{$campaign->id}", bagItems: [[
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 2.5,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ]])
        ->assertSet('modalConfirm', true)
        ->assertSee('1 item')
        ->set('participant_name', 'Maria Silva')
        ->set('method', 'organizer')
        ->assertSet('participant_whatsapp', '')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('public.campaigns.bag.finish', $campaign));

    $bag = Bag::query()->sole();

    expect(session('bag_finish'))->toBe([
        'method' => 'organizer',
        'campaign_name' => 'Jantar da Comunidade',
        'participant_name' => 'Maria Silva',
        'bag_code' => $bag->code,
    ])
        ->and($bag->participant_name)->toBe('Maria Silva')
        ->and($bag->participant_whatsapp)->toBeNull()
        ->and($bag->confirmed_by)->toBeNull()
        ->and($bag->confirmed_at)->toBeNull()
        ->and($item->refresh()->bagged_quantity)->toBe('2.5');

    expect($bag->items()->sole()->status)->toBe(BagItemStatusEnum::PENDING);
});

it('requires and validates whatsapp when public confirmation uses whatsapp', function (?string $whatsapp, string $rule) {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 8,
        'bagged_quantity' => 0,
    ]);

    Livewire::test('public.campaign.confirm-bag', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-confirm-bag.{$campaign->id}", bagItems: [[
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 1,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ]])
        ->set('participant_name', 'Maria Silva')
        ->set('method', 'whatsapp')
        ->set('participant_whatsapp', $whatsapp)
        ->call('submit')
        ->assertHasErrors(['participant_whatsapp' => [$rule]]);
})->with([
    'required' => ['', 'required_if'],
    'digits' => ['1111', 'digits_between'],
]);

it('creates a public bag with whatsapp confirmation and confirms it with the pin', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 8,
        'bagged_quantity' => 0,
    ]);

    $component = Livewire::test('public.campaign.confirm-bag', ['campaignId' => $campaign->id])
        ->dispatch("open-public-campaign-confirm-bag.{$campaign->id}", bagItems: [[
            'id' => $item->id,
            'name' => 'Arroz',
            'complement' => null,
            'quantity' => 1.5,
            'pendingBaggedQuantity' => 8,
            'unitAbbreviation' => 'un',
            'unitLabel' => 'unidades',
            'deliveryDate' => null,
        ]])
        ->set('participant_name', 'Maria Silva')
        ->set('method', 'whatsapp')
        ->set('participant_whatsapp', '(11) 99999-9999')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('modalConfirm', false)
        ->assertSet('pinModal', true)
        ->assertDispatched('public-campaign-bag-confirmation-code-generated');

    $bag = Bag::query()->sole();

    expect($bag->participant_whatsapp)->toBe('11999999999')
        ->and($bag->confirmation_code)->toHaveLength(6)
        ->and($bag->confirmed_by)->toBeNull();

    $component
        ->set('pin', '000000')
        ->call('confirmPin')
        ->assertHasErrors(['pin'])
        ->set('pin', $bag->confirmation_code)
        ->call('confirmPin')
        ->assertHasNoErrors()
        ->assertRedirect(route('public.campaigns.bag.finish', $campaign));

    $bag->refresh();

    expect(session('bag_finish'))->toBe([
        'method' => 'whatsapp',
        'campaign_name' => 'Jantar da Comunidade',
        'participant_name' => 'Maria Silva',
        'bag_code' => $bag->code,
    ])
        ->and($bag->confirmed_by)->toBe('participant')
        ->and($bag->confirmed_at)->not->toBeNull()
        ->and($bag->confirmation_code)->toBeNull()
        ->and($bag->items()->sole()->status)->toBe(BagItemStatusEnum::CONFIRMED);
});
