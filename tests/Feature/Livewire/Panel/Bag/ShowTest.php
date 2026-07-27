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
