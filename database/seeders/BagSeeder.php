<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class BagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::query()
            ->whereHas('items')
            ->with('items')
            ->get();

        foreach ($campaigns as $campaign) {
            $items = $campaign->items;
            $bags = [];
            for ($i = 0; $i < rand(1, 5); $i++) {
                $userId = array_rand([null, 1]);
                $confirmed = fake()->boolean();
                $bags[] = [
                    'campaign_id' => $campaign->id,
                    'code' => 'BA' . rand(1000, 9999),
                    'user_id' => $userId ? $userId : null,
                    'participant_name' => fake()->name(),
                    'participant_whatsapp' => '419' . rand(10000000, 99999999),
                    'confirmation_code' => rand(100000, 999999),
                    'confirmed_at' => $confirmed ? now() : null,
                    'confirmed_by' => $confirmed ? ($userId ? 'organizer' : 'participant') : null,
                ];
            }
            $createdBags = $campaign->bags()->createMany($bags);

            foreach ($createdBags as $bag) {
                $randomItems = $items->random(rand(1, min(3, $items->count())));
                foreach ($randomItems as $item) {
                    $quantity = rand(1, $item->required_quantity);
                    $bag->items()->create([
                        'campaign_item_id' => $item->id,
                        'quantity' => $quantity,
                        'status' => $bag->confirmed_at ? 'confirmed' : 'pending',
                    ]);
                    $item->update([
                        'bagged_quantity' => $item->bagged_quantity + $quantity,
                    ]);
                }
            }
        }
    }
}
