<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::with('items')->limit(1)->get();

        foreach ($campaigns as $campaign) {
            $items = $campaign->items;
            $bags = $campaign->bags()->createMany([
                [
                    'code' => 'BA6506',
                    'user_id' => 1,
                    'participant_name' => 'John Doe',
                    'confirmed_at' => now(),
                ],
                [
                    'code' => 'BA6507',
                    'participant_name' => 'Jane Smith',
                    'participant_whatsapp' => '0987654321',
                    'confirmation_code' => 'XYZ789',
                    'confirmed_at' => now(),
                ],
                [
                    'code' => 'BA6508',
                    'participant_name' => 'Lorem Ipsum',
                    'participant_whatsapp' => '4136853359',
                    'confirmation_code' => 'ABC123',
                ],
            ]);

            foreach ($bags as $bag) {
                $randomItems = $items->random(3);
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
