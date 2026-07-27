<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

function campaignForBagShowQueryPerformance(User $user): Campaign
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

it('renders the bag show page without duplicate header queries', function () {
    $user = User::factory()->create();
    $campaign = campaignForBagShowQueryPerformance($user);
    $bag = Bag::create([
        'campaign_id' => $campaign->id,
        'code' => 'ABC123',
        'participant_name' => 'Maria Silva',
        'participant_whatsapp' => '11 99999-9999',
        'confirmed_by' => 'organizer',
        'confirmed_at' => now(),
    ]);

    $item = CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
    ]);

    BagItem::create([
        'bag_id' => $bag->id,
        'campaign_item_id' => $item->id,
        'quantity' => 2,
        'status' => BagItemStatusEnum::CONFIRMED,
    ]);

    actingAs($user);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('panel.campaigns.bags.show', [$campaign, $bag]))
        ->assertSuccessful()
        ->assertSee('ABC123');

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(8);
});
