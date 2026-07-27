<?php

use App\Livewire\Panel\Campaign\Create;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('renders the create campaign component', function () {
    Livewire::test(Create::class)
        ->assertOk()
        ->assertViewIs('livewire.panel.campaign.create')
        ->assertSeeHtml('$wire.$errors.has(\'confirmation_deadline\')')
        ->assertSeeHtml('$wire.$errors.has(\'delivery_deadline\')');
});

it('initializes with a new active campaign', function () {
    Livewire::test(Create::class)
        ->assertSet('name', null)
        ->assertSet('description', null)
        ->assertSet('confirmation_deadline', null)
        ->assertSet('delivery_deadline', null)
        ->assertSet('is_active', true);
});

it('creates a campaign for the authenticated user', function () {
    $user = User::factory()->create();
    $confirmationDeadline = today()->addDays(10)->toDateString();
    $deliveryDeadline = today()->addDays(20)->toDateString();

    actingAs($user);

    Livewire::test(Create::class)
        ->set([
            'name' => 'Campanha de Inverno',
            'description' => 'Arrecadacao de roupas e cobertores.',
            'confirmation_deadline' => $confirmationDeadline,
            'delivery_deadline' => $deliveryDeadline,
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('created');

    assertDatabaseHas('campaigns', [
        'user_id' => $user->id,
        'name' => 'Campanha de Inverno',
        'description' => 'Arrecadacao de roupas e cobertores.',
        'confirmation_deadline' => $confirmationDeadline,
        'delivery_deadline' => $deliveryDeadline,
        'is_active' => true,
    ]);
});

it('requires campaign fields from the schema', function () {
    Livewire::test(Create::class)
        ->set('name', '')
        ->set('description', null)
        ->set('confirmation_deadline', '')
        ->set('delivery_deadline', '')
        ->set('is_active', true)
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'confirmation_deadline' => 'required',
            'delivery_deadline' => 'required',
        ]);
});

it('requires delivery deadline after or equal confirmation deadline', function () {
    $confirmationDeadline = today()->addDays(20)->toDateString();
    $deliveryDeadline = today()->addDays(10)->toDateString();

    Livewire::test(Create::class)
        ->set('name', 'Campanha de Inverno')
        ->set('confirmation_deadline', $confirmationDeadline)
        ->set('delivery_deadline', $deliveryDeadline)
        ->set('is_active', true)
        ->call('save')
        ->assertHasErrors(['delivery_deadline' => 'after_or_equal'])
        ->assertSee('O campo data limite de entrega deve ser uma data posterior ou igual a data limite de confirmação.');
});

it('shows today in portuguese for confirmation deadline date validation', function () {
    Livewire::test(Create::class)
        ->set('name', 'Campanha de Inverno')
        ->set('confirmation_deadline', today()->subDay()->toDateString())
        ->set('delivery_deadline', today()->toDateString())
        ->set('is_active', true)
        ->call('save')
        ->assertHasErrors(['confirmation_deadline' => 'after_or_equal'])
        ->assertSee('O campo data limite de confirmação deve ser uma data posterior ou igual a hoje.')
        ->assertDontSee('today')
        ->assertDontSee('confirmation deadline');
});

it('resets form after successful creation', function () {
    actingAs(User::factory()->create());

    Livewire::test(Create::class)
        ->set([
            'name' => 'Campanha de Inverno',
            'description' => 'Arrecadacao de roupas e cobertores.',
            'confirmation_deadline' => today()->addDays(10)->toDateString(),
            'delivery_deadline' => today()->addDays(20)->toDateString(),
            'is_active' => false,
        ])
        ->call('save')
        ->assertSet('name', null)
        ->assertSet('description', null)
        ->assertSet('confirmation_deadline', null)
        ->assertSet('delivery_deadline', null)
        ->assertSet('is_active', true);
});
