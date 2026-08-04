<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use App\Support\PublicCampaignBagSession;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        ->assertSee('Vou doar')
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
        ->assertSee('Vou doar');
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
            'note' => 'Preferência por arroz tipo 1.',
        ])
        ->assertSet('bagItems.0.name', 'Arroz')
        ->assertSet('bagItems.0.note', 'Preferência por arroz tipo 1.')
        ->assertSet('bagItems.0.quantity', 1.0)
        ->assertSee('Observações')
        ->assertSee('Preferência por arroz tipo 1.')
        ->dispatch("public-campaign-bag-increment.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems.0.quantity', 1.5)
        ->assertDispatched("public-campaign-bag-item-quantity-updated.{$campaign->id}")
        ->dispatch("public-campaign-bag-decrement.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems.0.quantity', 1.0)
        ->assertDispatched("public-campaign-bag-item-quantity-updated.{$campaign->id}")
        ->dispatch("public-campaign-bag-remove.{$campaign->id}", item: $item->id)
        ->assertSet('bagItems', [])
        ->assertDispatched("public-campaign-item-removed.{$campaign->id}");

    expect(PublicCampaignBagSession::get($campaign->id))->toBe([]);
});

it('restores public temporary bag items from session cache without mixing campaigns', function () {
    $user = User::factory()->create();

    $firstCampaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $secondCampaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Cafe da Comunidade',
        'description' => 'Nosso cafe será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $firstItem = CampaignItem::create([
        'campaign_id' => $firstCampaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    $secondItem = CampaignItem::create([
        'campaign_id' => $secondCampaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Feijao',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
    ]);

    PublicCampaignBagSession::put($firstCampaign->id, [[
        'id' => $firstItem->id,
        'name' => 'Arroz',
        'complement' => null,
        'quantity' => 2,
        'pendingBaggedQuantity' => 8,
        'unitAbbreviation' => 'un',
        'unitLabel' => 'unidades',
        'deliveryDate' => null,
    ]]);

    PublicCampaignBagSession::put($secondCampaign->id, [[
        'id' => $secondItem->id,
        'name' => 'Feijao',
        'complement' => null,
        'quantity' => 3,
        'pendingBaggedQuantity' => 8,
        'unitAbbreviation' => 'un',
        'unitLabel' => 'unidades',
        'deliveryDate' => null,
    ]]);

    Livewire::test(\App\Livewire\Public\Campaign\Show::class, ['campaign' => $firstCampaign])
        ->assertSet('bagItems.0.name', 'Arroz')
        ->assertSet('bagItems.0.quantity', 2.0)
        ->assertSet('bagItemIds', [$firstItem->id]);

    Livewire::test(\App\Livewire\Public\Campaign\Show::class, ['campaign' => $secondCampaign])
        ->assertSet('bagItems.0.name', 'Feijao')
        ->assertSet('bagItems.0.quantity', 3.0)
        ->assertSet('bagItemIds', [$secondItem->id]);
});

it('expires public temporary bag items after twelve hours', function () {
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

    PublicCampaignBagSession::put($campaign->id, [[
        'id' => $item->id,
        'name' => 'Arroz',
        'complement' => null,
        'quantity' => 2,
        'pendingBaggedQuantity' => 8,
        'unitAbbreviation' => 'un',
        'unitLabel' => 'unidades',
        'deliveryDate' => null,
    ]]);

    $this->travel(12)->hours();
    $this->travel(1)->second();

    Livewire::test(\App\Livewire\Public\Campaign\Show::class, ['campaign' => $campaign])
        ->assertSet('bagItems', [])
        ->assertSet('bagItemIds', []);
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
    Http::fake();

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
        'received_quantity' => 1,
    ]);

    PublicCampaignBagSession::put($campaign->id, [[
        'id' => $item->id,
        'name' => 'Arroz',
        'complement' => null,
        'quantity' => 2.5,
        'pendingBaggedQuantity' => 8,
        'unitAbbreviation' => 'un',
        'unitLabel' => 'unidades',
        'deliveryDate' => null,
    ]]);

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
        ->and($item->refresh()->bagged_quantity)->toBe('0.0')
        ->and($item->received_quantity)->toBe('1.0');

    expect($bag->items()->sole()->status)->toBe(BagItemStatusEnum::PENDING)
        ->and(PublicCampaignBagSession::get($campaign->id))->toBe([]);

    Http::assertNothingSent();
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
    config([
        'services.evolution.base_url' => 'https://evolution.test',
        'services.evolution.api_key' => 'evolution-api-key',
        'services.evolution.instance' => 'teste-josias',
        'services.evolution.country_code' => '55',
    ]);

    Http::fake([
        'https://evolution.test/message/sendText/teste-josias' => Http::response([
            'status' => 'sent',
        ]),
    ]);

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
        'received_quantity' => 1,
    ]);

    PublicCampaignBagSession::put($campaign->id, [[
        'id' => $item->id,
        'name' => 'Arroz',
        'complement' => null,
        'quantity' => 1.5,
        'pendingBaggedQuantity' => 8,
        'unitAbbreviation' => 'un',
        'unitLabel' => 'unidades',
        'deliveryDate' => null,
    ]]);

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
        ->assertSet('pinModal', true);

    $bag = Bag::query()->sole();

    Http::assertSent(function (Request $request) use ($bag): bool {
        $message = $request['text'];

        return $request->url() === 'https://evolution.test/message/sendText/teste-josias'
            && $request->hasHeader('apikey', 'evolution-api-key')
            && $request['number'] === '5511999999999'
            && str_contains($message, 'Olá, Maria Silva! 😊')
            && str_contains($message, 'campanha Jantar da Comunidade')
            && str_contains($message, (string) $bag->confirmation_code)
            && str_contains($message, '- 1,5 un Arroz');
    });

    expect($bag->participant_whatsapp)->toBe('11999999999')
        ->and($bag->confirmation_code)->toHaveLength(5)
        ->and($bag->confirmed_by)->toBeNull()
        ->and($item->refresh()->bagged_quantity)->toBe('0.0')
        ->and($item->received_quantity)->toBe('1.0')
        ->and(PublicCampaignBagSession::get($campaign->id))->not->toBe([]);

    $component
        ->set('pin', '000000')
        ->call('confirmPin')
        ->assertHasErrors(['pin']);

    expect($item->refresh()->bagged_quantity)->toBe('0.0')
        ->and($item->received_quantity)->toBe('1.0');

    $component
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
        ->and($bag->items()->sole()->status)->toBe(BagItemStatusEnum::CONFIRMED)
        ->and($item->refresh()->bagged_quantity)->toBe('1.5')
        ->and($item->received_quantity)->toBe('1.0')
        ->and(PublicCampaignBagSession::get($campaign->id))->toBe([]);
});

it('requires an absolute evolution api url before sending whatsapp confirmation', function () {
    config([
        'services.evolution.base_url' => '76BF00C94356-4EB5-B39A-26B80FF7E501',
        'services.evolution.api_key' => 'evolution-api-key',
        'services.evolution.instance' => 'teste-josias',
        'services.evolution.country_code' => '55',
    ]);

    Http::fake();
    Log::spy();

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
        ->assertSet('method', 'organizer')
        ->assertSet('participant_whatsapp', '')
        ->assertSet('modalConfirm', true)
        ->assertSet('pinModal', false)
        ->assertSee('Não conseguimos enviar o código pelo WhatsApp agora.');

    Http::assertNothingSent();

    Log::shouldHaveReceived('error')
        ->once()
        ->with(
            'Failed to send public bag confirmation code through Evolution API.',
            \Mockery::on(fn (array $context): bool => $context['campaign_id'] === $campaign->id
                && $context['evolution_instance'] === 'teste-josias'
                && $context['exception'] === \RuntimeException::class
                && $context['message'] === 'EVOLUTION_API_URL must be an absolute URL with http:// or https://.'),
        );

    expect(Bag::withTrashed()->count())->toBe(0);
});

it('falls back to organizer confirmation when evolution api rejects the whatsapp message', function () {
    config([
        'services.evolution.base_url' => 'https://evolution.test',
        'services.evolution.api_key' => 'evolution-api-key',
        'services.evolution.instance' => 'teste-josias',
        'services.evolution.country_code' => '55',
    ]);

    Http::fake([
        'https://evolution.test/message/sendText/teste-josias' => Http::response([
            'status' => 400,
            'error' => 'Bad Request',
            'response' => [
                'message' => [
                    ['instance requires property "text"'],
                ],
            ],
        ], 400),
    ]);
    Log::spy();

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
        ->assertSet('method', 'organizer')
        ->assertSet('participant_whatsapp', '')
        ->assertSet('modalConfirm', true)
        ->assertSet('pinModal', false)
        ->assertSee('Não conseguimos enviar o código pelo WhatsApp agora.');

    Http::assertSentCount(1);

    Log::shouldHaveReceived('error')
        ->once()
        ->with(
            'Failed to send public bag confirmation code through Evolution API.',
            \Mockery::on(fn (array $context): bool => $context['campaign_id'] === $campaign->id
                && $context['participant_whatsapp'] === '11999999999'
                && $context['evolution_instance'] === 'teste-josias'
                && $context['status'] === 400
                && $context['response']['error'] === 'Bad Request'),
        );

    expect(Bag::withTrashed()->count())->toBe(0);
});
