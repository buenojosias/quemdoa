<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampaignInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = \App\Models\Campaign::all();

        foreach ($campaigns as $campaign) {
            \App\Models\CampaignInfo::factory()->count(3)->create([
                'campaign_id' => $campaign->id,
            ]);
        }
    }
}
