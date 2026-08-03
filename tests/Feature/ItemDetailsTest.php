<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function campaignForItemDetails(User $user): Campaign
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

function itemForItemDetails(Campaign $campaign): CampaignItem
{
    return CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'complement' => 'Pacote de 5kg',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 6,
        'received_quantity' => 2,
        'delivery_date' => today()->addDays(5)->toDateString(),
        'note' => 'Pacotes fechados.',
    ]);
}

function bagForItemDetails(Campaign $campaign, string $participantName): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => $participantName,
    ]);
}

function bagItemForItemDetails(
    Bag $bag,
    CampaignItem $item,
    BagItemStatusEnum $status,
    int|float $quantity = 1,
): BagItem {
    return BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $item->id,
        'quantity' => $quantity,
        'status' => $status,
    ]);
}

it('loads real item data when opening details', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemDetails($user);
    $item = itemForItemDetails($campaign);

    bagItemForItemDetails(bagForItemDetails($campaign, 'Maria'), $item, BagItemStatusEnum::PENDING);
    bagItemForItemDetails(bagForItemDetails($campaign, 'Joao'), $item, BagItemStatusEnum::CONFIRMED);
    bagItemForItemDetails(bagForItemDetails($campaign, 'Ana'), $item, BagItemStatusEnum::RECEIVED);

    actingAs($user);

    Livewire::test('panel.campaign.item-details', ['campaignId' => $campaign->id])
        ->assertSet('slide', false)
        ->dispatch("open-item-details.{$campaign->id}", item: $item->id)
        ->assertSet('slide', true)
        ->assertSet('itemId', $item->id)
        ->assertSet('category', CategoryEnum::FOODS->value)
        ->assertSet('categoryIllustration', 'foods.png')
        ->assertSet('name', 'Arroz')
        ->assertSet('complement', 'Pacote de 5kg')
        ->assertSet('unitLabel', 'kilogramas')
        ->assertSet('unitAbbreviation', 'kg')
        ->assertSet('requiredQuantity', '10')
        ->assertSet('baggedQuantity', '6')
        ->assertSet('receivedQuantity', '2')
        ->assertSet('pendingQuantity', '4')
        ->assertSet('baggedPercent', 60)
        ->assertSet('receivedPercent', 20)
        ->assertSet('pendingPercent', 40)
        ->assertSet('deliveryDate', today()->addDays(5)->format('d/m/Y'))
        ->assertSet('note', 'Pacotes fechados.')
        ->assertSet('statusLabel', 'Coletando')
        ->assertSet('pendingBagsCount', 1)
        ->assertSet('confirmedBagsCount', 1)
        ->assertSet('receivedBagsCount', 1)
        ->assertSee('Arroz')
        ->assertSee('Pacote de 5kg')
        ->assertSee('Pacotes fechados.')
        ->assertSee('1 sacola');
});

it('marks the item as complete when the bagged quantity reaches the required quantity', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemDetails($user);
    $item = itemForItemDetails($campaign);

    $item->update([
        'bagged_quantity' => 10,
        'received_quantity' => 7.5,
    ]);

    actingAs($user);

    Livewire::test('panel.campaign.item-details', ['campaignId' => $campaign->id])
        ->dispatch("open-item-details.{$campaign->id}", item: $item->id)
        ->assertSet('statusLabel', 'Meta atingida')
        ->assertSet('statusColor', 'green')
        ->assertSet('baggedPercent', 100)
        ->assertSet('receivedPercent', 75)
        ->assertSet('pendingQuantity', '0')
        ->assertSee('Meta atingida');
});

it('does not load details for an item from another campaign', function () {
    $owner = User::factory()->create();
    $campaign = campaignForItemDetails($owner);
    $otherCampaign = campaignForItemDetails($owner);
    $item = itemForItemDetails($otherCampaign);

    actingAs($owner);

    expect(fn () => Livewire::test('panel.campaign.item-details', ['campaignId' => $campaign->id])
        ->dispatch("open-item-details.{$campaign->id}", item: $item->id))
        ->toThrow(ModelNotFoundException::class);
});

it('mounts item details inside the campaign items table', function () {
    $user = User::factory()->create();
    $campaign = campaignForItemDetails($user);
    itemForItemDetails($campaign);

    actingAs($user);

    Livewire::test('panel.tables.campaign-items', ['campaign' => $campaign])
        ->assertSee('Arroz')
        ->assertSee('Detalhes')
        ->assertSeeLivewire('panel.campaign.item-details');
});
