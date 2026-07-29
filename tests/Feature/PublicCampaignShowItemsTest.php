<?php

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\User;

it('renders campaign items grouped by category with item details', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Jantar da Comunidade',
        'description' => 'Nosso jantar será um momento especial.',
        'confirmation_deadline' => today()->addDays(5)->toDateString(),
        'delivery_deadline' => today()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'complement' => 'Pacote 5kg',
        'unit' => UnitEnum::UNIT->value,
        'required_quantity' => 20,
        'bagged_quantity' => 12,
        'delivery_date' => today()->addDays(10)->toDateString(),
        'note' => 'Preferência por arroz tipo 1.',
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Carne bovina',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 12,
        'bagged_quantity' => 0,
    ]);

    CampaignItem::create([
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::DRINKS->value,
        'name' => 'Refrigerante',
        'unit' => UnitEnum::L->value,
        'required_quantity' => 10,
        'bagged_quantity' => 14,
    ]);

    $this->get(route('public.campaigns.show', $campaign))
        ->assertSuccessful()
        ->assertSee('Jantar da Comunidade')
        ->assertSeeInOrder(['Comidas', 'Arroz', 'Carne bovina', 'Bebidas', 'Refrigerante'])
        ->assertSee('assets/images/category-illustrations/foods.png', false)
        ->assertSee('assets/images/category-illustrations/drinks.png', false)
        ->assertSee('2 itens')
        ->assertSee('Pacote 5kg')
        ->assertSee('8 un pendente')
        ->assertSee('20 un.')
        ->assertSee('12 un. (60%)')
        ->assertSee('Preferência por arroz tipo 1.')
        ->assertSee('Na sacola')
        ->assertSee('0 l pendente');
});
