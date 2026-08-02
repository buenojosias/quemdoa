<?php

use App\Livewire\Panel\Campaign\Edit;
use App\Livewire\Panel\Campaign\Show;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

it('renders the edit campaign modal on the campaign show page', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()
        ->for($user)
        ->create();

    actingAs($user);

    get(route('panel.campaigns.show', $campaign))
        ->assertSuccessful()
        ->assertSeeLivewire(Show::class)
        ->assertSeeLivewire(Edit::class)
        ->assertSee('Editar campanha');
});

it('opens the modal with the campaign data', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()
        ->for($user)
        ->create([
            'name' => 'Campanha Original',
            'institution' => 'Paroquia Central',
            'group' => 'Pastoral Social',
            'description' => 'Descricao original',
            'confirmation_deadline' => today()->addDays(10)->toDateString(),
            'delivery_deadline' => today()->addDays(20)->toDateString(),
            'is_active' => true,
        ]);

    actingAs($user);

    Livewire::test(Edit::class, ['campaign' => $campaign])
        ->assertSet('modal', false)
        ->dispatch("open-campaign-edit.{$campaign->id}")
        ->assertSet('modal', true)
        ->assertSet('name', 'Campanha Original')
        ->assertSet('institution', 'Paroquia Central')
        ->assertSet('group', 'Pastoral Social')
        ->assertSet('description', 'Descricao original')
        ->assertSet('confirmation_deadline', today()->addDays(10)->toDateString())
        ->assertSet('delivery_deadline', today()->addDays(20)->toDateString())
        ->assertSet('is_active', true);
});

it('updates a campaign for the authenticated user', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()
        ->for($user)
        ->create([
            'confirmation_deadline' => today()->addDays(10)->toDateString(),
            'delivery_deadline' => today()->addDays(20)->toDateString(),
            'is_active' => true,
        ]);
    $confirmationDeadline = today()->addDays(15)->toDateString();
    $deliveryDeadline = today()->addDays(25)->toDateString();

    actingAs($user);

    Livewire::test(Edit::class, ['campaign' => $campaign])
        ->dispatch("open-campaign-edit.{$campaign->id}")
        ->set([
            'name' => 'Campanha Atualizada',
            'institution' => 'Nova Instituicao',
            'group' => 'Novo Grupo',
            'description' => 'Descricao atualizada.',
            'confirmation_deadline' => $confirmationDeadline,
            'delivery_deadline' => $deliveryDeadline,
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertDispatched('updated')
        ->assertDispatched("campaign-updated.{$campaign->id}");

    assertDatabaseHas('campaigns', [
        'id' => $campaign->id,
        'user_id' => $user->id,
        'name' => 'Campanha Atualizada',
        'institution' => 'Nova Instituicao',
        'group' => 'Novo Grupo',
        'description' => 'Descricao atualizada.',
        'confirmation_deadline' => $confirmationDeadline,
        'delivery_deadline' => $deliveryDeadline,
        'is_active' => false,
    ]);
});

it('refreshes the campaign info when the campaign is updated', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()
        ->for($user)
        ->create([
            'name' => 'Campanha Original',
            'institution' => 'Instituicao Original',
        ]);

    actingAs($user);

    $component = Livewire::test('panel.campaign.info', ['campaign' => $campaign])
        ->assertSee('Campanha Original')
        ->assertSee('Instituicao Original');

    $campaign->update([
        'name' => 'Campanha Atualizada',
        'institution' => 'Instituicao Atualizada',
    ]);

    $component
        ->dispatch("campaign-updated.{$campaign->id}")
        ->assertSee('Campanha Atualizada')
        ->assertSee('Instituicao Atualizada')
        ->assertDontSee('Campanha Original');
});

it('validates the campaign deadlines while editing', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()
        ->for($user)
        ->create();

    actingAs($user);

    Livewire::test(Edit::class, ['campaign' => $campaign])
        ->dispatch("open-campaign-edit.{$campaign->id}")
        ->set('name', 'Campanha Atualizada')
        ->set('confirmation_deadline', today()->addDays(20)->toDateString())
        ->set('delivery_deadline', today()->addDays(10)->toDateString())
        ->call('save')
        ->assertHasErrors(['delivery_deadline' => 'after_or_equal'])
        ->assertSee('O campo data limite de entrega deve ser uma data posterior ou igual a data limite de confirmação.');
});
