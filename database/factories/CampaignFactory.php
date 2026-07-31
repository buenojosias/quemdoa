<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $confirmation_deadline = fake()->dateTimeBetween('+1 week', '+1 month');
        return [
            'user_id' => 1,
            'name' => fake()->sentence(3), // Nome da campanha
            'institution' => fake()->optional()->company(), // Nome da instituição beneficiada
            'group' => fake()->optional()->word(), // Grupo ou comunidade beneficiada
            'description' => fake()->paragraph(), // Descrição da campanha
            'confirmation_deadline' => $confirmation_deadline, // Data limite para confirmação da campanha
            'delivery_deadline' => fake()->dateTimeBetween($confirmation_deadline, $confirmation_deadline->modify('+1 week')), // Data de término da campanha
        ];
    }
}
