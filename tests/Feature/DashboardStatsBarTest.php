<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Livewire\Livewire;

it('renders dashboard stats with real user data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $activeCampaign = campaignForDashboardStats($user, true);
    $anotherActiveCampaign = campaignForDashboardStats($user, true);
    $inactiveCampaign = campaignForDashboardStats($user, false);
    $otherUserCampaign = campaignForDashboardStats($otherUser, true);

    createBagForDashboardStats($activeCampaign, BagItemStatusEnum::PENDING);
    createBagForDashboardStats($activeCampaign, BagItemStatusEnum::RECEIVED);
    createBagForDashboardStats($anotherActiveCampaign, BagItemStatusEnum::CONFIRMED);
    createBagForDashboardStats($inactiveCampaign, BagItemStatusEnum::PENDING);
    createBagForDashboardStats($inactiveCampaign, BagItemStatusEnum::RECEIVED);
    createBagForDashboardStats($otherUserCampaign, BagItemStatusEnum::PENDING);
    createBagForDashboardStats($otherUserCampaign, BagItemStatusEnum::RECEIVED);

    Livewire::actingAs($user)
        ->test('panel.dashboard.stats-bar')
        ->assertSee('Campanhas ativas')
        ->assertSee('Sacolas cadastradas')
        ->assertSee('Sacolas a confirmar')
        ->assertSee('Sacolas recebidas')
        ->assertSeeInOrder([
            'Campanhas ativas',
            '2',
            'Sacolas cadastradas',
            '3',
            'Sacolas a confirmar',
            '1',
            'Sacolas recebidas',
            '2',
        ]);
});

function campaignForDashboardStats(User $user, bool $isActive): Campaign
{
    return Campaign::query()->create([
        'user_id' => $user->id,
        'name' => fake()->sentence(3),
        'description' => fake()->paragraph(),
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => $isActive,
    ]);
}

function createBagForDashboardStats(Campaign $campaign, BagItemStatusEnum $status): Bag
{
    $item = CampaignItem::query()->create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => fake()->word(),
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 10,
    ]);

    $bag = Bag::query()->create([
        'campaign_id' => $campaign->id,
        'code' => fake()->unique()->bothify('??####'),
        'participant_name' => fake()->name(),
    ]);

    $bag->items()->create([
        'campaign_item_id' => $item->id,
        'quantity' => 1,
        'status' => $status,
    ]);

    return $bag;
}
