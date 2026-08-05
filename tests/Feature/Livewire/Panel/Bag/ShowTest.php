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

use function Pest\Laravel\actingAs;

function campaignForPanelBagShow(User $user): Campaign
{
    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Alimentos',
        'description' => 'Arrecadacao de alimentos para familias.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

function bagForPanelBagShow(Campaign $campaign, string $participantName = 'Maria Silva'): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => $participantName,
        'participant_whatsapp' => '11 99999-9999',
        'confirmed_by' => 'organizer',
        'confirmed_at' => now(),
    ]);
}

function itemForPanelBagShow(Campaign $campaign): CampaignItem
{
    return CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
    ]);
}

it('renders an authenticated user bag within its campaign', function () {
    $user = User::factory()->create();
    $campaign = campaignForPanelBagShow($user);
    $bag = bagForPanelBagShow($campaign);
    $item = itemForPanelBagShow($campaign);

    BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $item->id,
        'quantity' => 2,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    actingAs($user);

    Livewire::test(Show::class, [
        'campaign' => $campaign->id,
        'bag' => $bag->id,
    ])
        ->assertOk()
        ->assertViewIs('livewire.panel.bag.show')
        ->assertSet('campaignId', (string) $campaign->id)
        ->assertSet('bagId', (string) $bag->id)
        ->assertSee('Campanha de Alimentos')
        ->assertSee('Sacola')
        ->assertSee($bag->code)
        ->assertSee('Maria Silva')
        ->assertSee('Itens da sacola')
        ->assertSeeLivewire('panel.bag.add-item')
        ->assertSeeLivewire('panel.tables.bag-items');
});

it('confirms a pending bag and recalculates campaign item quantities', function () {
    $user = User::factory()->create();
    $campaign = campaignForPanelBagShow($user);
    $bag = Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => 'Maria Silva',
        'participant_whatsapp' => '11 99999-9999',
        'confirmation_code' => '12345',
    ]);

    $rice = itemForPanelBagShow($campaign);
    $beans = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Feijao',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
    ]);

    BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $rice->id,
        'quantity' => 2,
        'status' => BagItemStatusEnum::PENDING,
    ]);

    BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $beans->id,
        'quantity' => 1.5,
        'status' => BagItemStatusEnum::RECEIVED,
    ]);

    $otherBag = bagForPanelBagShow($campaign, 'Joao Silva');

    BagItem::create([
        'bag_id' => $otherBag->id,
        'campaign_item_id' => $rice->id,
        'quantity' => 3,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    BagItem::create([
        'bag_id' => $otherBag->id,
        'campaign_item_id' => $rice->id,
        'quantity' => 4,
        'status' => BagItemStatusEnum::PENDING,
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, [
            'campaign' => $campaign->id,
            'bag' => $bag->id,
        ])
        ->call('confirm')
        ->assertSee('Confirmada')
        ->assertDontSee('Confirmar sacola')
        ->assertDispatched("bag-status-updated.{$bag->id}")
        ->assertDispatched("campaign-bag-status-updated.{$campaign->id}")
        ->assertDispatched("bag-confirmed.{$bag->id}")
        ->assertDispatched("campaign-bag-confirmed.{$campaign->id}")
        ->assertDispatched("item-created.{$campaign->id}")
        ->assertDispatched(
            "campaign-bag-item-quantity-updated.{$campaign->id}",
            item: $rice->id,
            status: BagItemStatusEnum::CONFIRMED->value,
        )
        ->assertDispatched(
            "campaign-bag-item-quantity-updated.{$campaign->id}",
            item: $beans->id,
            status: BagItemStatusEnum::CONFIRMED->value,
        );

    expect($bag->refresh()->confirmed_by)->toBe('organizer')
        ->and($bag->confirmed_at)->not->toBeNull()
        ->and($bag->confirmation_code)->toBeNull()
        ->and($bag->items()->where('campaign_item_id', $rice->id)->sole()->status)->toBe(BagItemStatusEnum::CONFIRMED)
        ->and($bag->items()->where('campaign_item_id', $beans->id)->sole()->status)->toBe(BagItemStatusEnum::RECEIVED)
        ->and($rice->refresh()->bagged_quantity)->toBe('5.0')
        ->and($rice->received_quantity)->toBe('0.0')
        ->and($beans->refresh()->bagged_quantity)->toBe('1.5')
        ->and($beans->received_quantity)->toBe('1.5');
});

it('renders a received bag status', function () {
    $user = User::factory()->create();
    $campaign = campaignForPanelBagShow($user);
    $bag = bagForPanelBagShow($campaign);

    $bag->update([
        'received_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, [
            'campaign' => $campaign->id,
            'bag' => $bag->id,
        ])
        ->assertSee('Recebida');
});

it('refreshes the status badge when the bag updated timestamp changes', function () {
    $user = User::factory()->create();
    $campaign = campaignForPanelBagShow($user);
    $bag = bagForPanelBagShow($campaign);

    $component = Livewire::actingAs($user)
        ->test(Show::class, [
            'campaign' => $campaign->id,
            'bag' => $bag->id,
        ])
        ->assertSee('Confirmada');

    $this->travel(1)->second();

    $bag->update([
        'received_at' => now(),
    ]);

    $component
        ->dispatch("bag-item-received.{$bag->id}")
        ->assertSee('Recebida')
        ->assertDispatched("bag-status-updated.{$bag->id}")
        ->assertDispatched("campaign-bag-status-updated.{$campaign->id}");
});

it('does not render a bag from another authenticated user campaign', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $campaign = campaignForPanelBagShow($owner);
    $bag = bagForPanelBagShow($campaign);

    actingAs($intruder);

    $this->get(route('panel.campaigns.bags.show', [$campaign, $bag]))
        ->assertNotFound();
});

it('does not render a bag that does not belong to the route campaign', function () {
    $user = User::factory()->create();
    $campaign = campaignForPanelBagShow($user);
    $otherCampaign = campaignForPanelBagShow($user);
    $bag = bagForPanelBagShow($otherCampaign);

    actingAs($user);

    $this->get(route('panel.campaigns.bags.show', [$campaign, $bag]))
        ->assertNotFound();
});
