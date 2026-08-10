<?php

namespace Database\Factories;

use App\Models\CampaignInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignInfo>
 */
class CampaignInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
