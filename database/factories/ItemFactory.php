<?php

namespace Database\Factories;

use App\Enums\CategoryEnum;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $required_quantity = $this->faker->numberBetween(1, 10);
        return [
            'campaign_id' => \App\Models\Campaign::first()->id ?? \App\Models\Campaign::factory()->create()->id,
            'category' => $this->faker->randomElement(CategoryEnum::cases()),
            'name' => ucfirst($this->faker->words(3, true)),
            'complement' => $this->faker->optional()->sentence(3),
            'unit' => $this->faker->randomElement(array_map(fn($case) => $case->value, \App\Enums\UnitEnum::cases())),
            'required_quantity' => $required_quantity,
            'bagged_quantity' => 0,
            'received_quantity' => 0,
            'delivery_date' => $this->faker->optional()->dateTimeBetween('+1 week', '+2 week'),
            'note' => $this->faker->optional()->sentence()
        ];
    }
}
