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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function campaignForDeleteBag(User $user): Campaign
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

function itemForDeleteBag(Campaign $campaign, string $name = 'Arroz'): CampaignItem
{
    return CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => $name,
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 0,
        'received_quantity' => 0,
    ]);
}

function bagForDeleteBag(Campaign $campaign, string $participantName = 'Maria'): Bag
{
    return Bag::create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => $participantName,
        'confirmed_by' => 'organizer',
        'confirmed_at' => now(),
    ]);
}

function bagItemForDeleteBag(
    Bag $bag,
    CampaignItem $item,
    BagItemStatusEnum $status,
    int|float $quantity,
): BagItem {
    return BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $item->id,
        'quantity' => $quantity,
        'status' => $status,
    ]);
}

it('opens the delete dialog and warns when the bag has received items', function () {
    $user = User::factory()->create();
    $campaign = campaignForDeleteBag($user);
    $bag = bagForDeleteBag($campaign);
    $item = itemForDeleteBag($campaign);

    bagItemForDeleteBag($bag, $item, BagItemStatusEnum::RECEIVED, 2);

    Livewire::actingAs($user)
        ->test('panel.bag.delete-bag')
        ->dispatch('open-delete-bag', bag: $bag->id)
        ->assertSet('bagId', $bag->id)
        ->assertSet('campaignId', $campaign->id)
        ->assertSet('hasReceivedItems', true);
});

it('opens the delete dialog without the received items warning when none were received', function () {
    $user = User::factory()->create();
    $campaign = campaignForDeleteBag($user);
    $bag = bagForDeleteBag($campaign);
    $item = itemForDeleteBag($campaign);

    bagItemForDeleteBag($bag, $item, BagItemStatusEnum::CONFIRMED, 2);

    Livewire::actingAs($user)
        ->test('panel.bag.delete-bag')
        ->dispatch('open-delete-bag', bag: $bag->id)
        ->assertSet('hasReceivedItems', false);
});

it('deletes a bag and recalculates bagged and received quantities from confirmed and received items', function () {
    $user = User::factory()->create();
    $campaign = campaignForDeleteBag($user);
    $rice = itemForDeleteBag($campaign, 'Arroz');
    $beans = itemForDeleteBag($campaign, 'Feijao');
    $deletedBag = bagForDeleteBag($campaign, 'Maria');
    $remainingBag = bagForDeleteBag($campaign, 'Joao');

    bagItemForDeleteBag($deletedBag, $rice, BagItemStatusEnum::CONFIRMED, 3);
    bagItemForDeleteBag($deletedBag, $beans, BagItemStatusEnum::RECEIVED, 4);
    bagItemForDeleteBag($deletedBag, $rice, BagItemStatusEnum::PENDING, 5);
    bagItemForDeleteBag($remainingBag, $rice, BagItemStatusEnum::CONFIRMED, 2);
    bagItemForDeleteBag($remainingBag, $beans, BagItemStatusEnum::RECEIVED, 1.5);

    $rice->update(['bagged_quantity' => 5, 'received_quantity' => 0]);
    $beans->update(['bagged_quantity' => 5.5, 'received_quantity' => 5.5]);

    Livewire::actingAs($user)
        ->test('panel.bag.delete-bag')
        ->dispatch('open-delete-bag', bag: $deletedBag->id)
        ->call('delete')
        ->assertDispatched("bag-deleted.{$campaign->id}")
        ->assertSet('bagId', null)
        ->assertSet('campaignId', null);

    expect(Bag::query()->whereKey($deletedBag->id)->exists())->toBeFalse()
        ->and(BagItem::query()->where('bag_id', $deletedBag->id)->exists())->toBeFalse()
        ->and($rice->refresh()->bagged_quantity)->toBe('2.0')
        ->and($rice->received_quantity)->toBe('0.0')
        ->and($beans->refresh()->bagged_quantity)->toBe('1.5')
        ->and($beans->received_quantity)->toBe('1.5');
});

it('does not allow deleting a bag from another organizer campaign', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $campaign = campaignForDeleteBag($owner);
    $bag = bagForDeleteBag($campaign);

    expect(fn () => Livewire::actingAs($intruder)
        ->test('panel.bag.delete-bag')
        ->dispatch('open-delete-bag', bag: $bag->id))
        ->toThrow(ModelNotFoundException::class);
});

it('refreshes the bags table when a bag is deleted', function () {
    $user = User::factory()->create();
    $campaign = campaignForDeleteBag($user);

    bagForDeleteBag($campaign, 'Maria');

    Livewire::actingAs($user)
        ->test('panel.tables.campaign-bags', ['campaign' => $campaign])
        ->assertSee('Maria')
        ->dispatch("bag-deleted.{$campaign->id}")
        ->assertOk();
});

it('redirects the bag show page after the current bag is deleted', function () {
    $user = User::factory()->create();
    $campaign = campaignForDeleteBag($user);
    $bag = bagForDeleteBag($campaign);

    actingAs($user);

    Livewire::test(Show::class, [
        'campaign' => $campaign->id,
        'bag' => $bag->id,
    ])
        ->dispatch("bag-deleted.{$campaign->id}")
        ->assertRedirect(route('panel.campaigns.bags', $campaign));
});
