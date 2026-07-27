<?php

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Livewire\Panel\Item\Create;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

function campaignForItemCreate(): Campaign
{
    $user = User::factory()->create();

    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Inverno',
        'description' => 'Arrecadacao de roupas e cobertores.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

it('renders the item create component with a modal', function () {
    Livewire::test(Create::class, ['campaignId' => campaignForItemCreate()->id])
        ->assertOk()
        ->assertViewIs('livewire.panel.item.create')
        ->assertSee('Adicionar item')
        ->assertSet('modal', false);
});

it('includes an empty select option for category and unit fields', function () {
    $component = new Create();

    expect($component->categoryOptions()[0])->toBe([
        'label' => 'Selecione',
        'value' => '',
    ])->and($component->unitOptions()[0])->toBe([
        'label' => 'Selecione',
        'value' => '',
    ]);
});

it('creates an item, keeps the modal open, resets fields except category, and dispatches table refresh', function () {
    $campaign = campaignForItemCreate();

    actingAs($campaign->user);

    Livewire::test(Create::class, ['campaignId' => $campaign->id])
        ->set('modal', true)
        ->set([
            'category' => CategoryEnum::FOODS->value,
            'name' => 'Arroz',
            'unit' => UnitEnum::KG->value,
            'required_quantity' => 10,
            'delivery_date' => today()->addDays(5)->toDateString(),
            'note' => 'Pacotes fechados.',
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', true)
        ->assertSet('category', CategoryEnum::FOODS->value)
        ->assertSet('name', null)
        ->assertSet('unit', null)
        ->assertSet('required_quantity', null)
        ->assertSet('delivery_date', null)
        ->assertSet('note', null)
        ->assertSet('successMessage', 'Item adicionado com sucesso!')
        ->assertDispatched("item-created.{$campaign->id}");

    assertDatabaseHas('campaign_items', [
        'campaign_id' => $campaign->id,
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'delivery_date' => today()->addDays(5)->startOfDay()->toDateTimeString(),
        'note' => 'Pacotes fechados.',
    ]);
});

it('requires item fields from the schema', function () {
    Livewire::test(Create::class, ['campaignId' => campaignForItemCreate()->id])
        ->set('category', '')
        ->set('name', '')
        ->set('unit', '')
        ->set('required_quantity', null)
        ->call('save')
        ->assertHasErrors([
            'category' => 'required',
            'name' => 'required',
            'unit' => 'required',
            'required_quantity' => 'required',
        ]);
});

it('refreshes the campaign items table when an item is created', function () {
    $campaign = campaignForItemCreate();

    $component = Livewire::test('panel.campaign.items-table', ['campaignId' => $campaign->id])
        ->assertDontSee('Arroz');

    $campaign->items()->create([
        'category' => CategoryEnum::FOODS->value,
        'name' => 'Arroz',
        'unit' => UnitEnum::KG->value,
        'required_quantity' => 10,
        'delivery_date' => today()->addDays(5)->toDateString(),
        'note' => 'Pacotes fechados.',
    ]);

    $component
        ->dispatch("item-created.{$campaign->id}")
        ->assertSee('Arroz')
        ->assertSee('Comidas')
        ->assertSee('10.0 kg');
});
