<?php

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

function campaignForDashboardActiveCampaigns(User $user, string $name, int $createdDaysAgo, bool $isActive = true): Campaign
{
    return Campaign::query()->create([
        'user_id' => $user->id,
        'name' => $name,
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => $isActive,
        'created_at' => now()->subDays($createdDaysAgo),
        'updated_at' => now()->subDays($createdDaysAgo),
    ]);
}

it('renders the four most recent campaigns owned by the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    campaignForDashboardActiveCampaigns($user, 'Campanha Antiga Oculta', 5);
    $fourth = campaignForDashboardActiveCampaigns($user, 'Campanha 4', 4);
    $third = campaignForDashboardActiveCampaigns($user, 'Campanha 3', 3, false);
    $second = campaignForDashboardActiveCampaigns($user, 'Campanha 2', 2);
    $first = campaignForDashboardActiveCampaigns($user, 'Campanha 1', 1);
    campaignForDashboardActiveCampaigns($otherUser, 'Campanha de Outro Usuario', 0);

    $first->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'bagged_quantity' => 5,
    ]);

    Livewire::actingAs($user)
        ->test('panel.dashboard.active-campaigns')
        ->assertSee('Campanhas recentes')
        ->assertSeeInOrder([
            'Campanha 1',
            'Campanha 2',
            'Campanha 3',
            'Campanha 4',
        ])
        ->assertSee('Ativa')
        ->assertSee('Inativa')
        ->assertDontSee('Campanha Antiga Oculta')
        ->assertDontSee('Campanha de Outro Usuario');
});
