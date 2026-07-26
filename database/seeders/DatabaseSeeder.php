<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Josias Bueno',
            'email' => 'josias@email.com',
            'password' => bcrypt('12345678'),
        ]);

        $this->call([
            CampaignSeeder::class,
            ItemSeeder::class,
            BagSeeder::class,
        ]);
    }
}
