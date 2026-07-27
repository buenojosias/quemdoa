<?php

use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;
use Database\Seeders\BagSeeder;

it('skips campaigns without items when creating bags', function () {
    $user = User::factory()->create();

    $campaignWithoutItems = Campaign::factory()->create([
        'user_id' => $user->id,
    ]);

    $campaignWithItems = Campaign::factory()
        ->has(CampaignItem::factory()->count(3), 'items')
        ->create([
            'user_id' => $user->id,
        ]);

    $this->seed(BagSeeder::class);

    expect($campaignWithoutItems->bags()->count())->toBe(0)
        ->and($campaignWithItems->bags()->count())->toBeGreaterThan(0);
});
