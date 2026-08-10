<?php

use App\Models\Campaign;
use App\Models\CampaignInfo;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function campaignForInfoAdd(): Campaign
{
    $user = User::factory()->create();

    return Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Alimentos',
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);
}

it('opens the add information modal from the campaign infos list', function () {
    $campaign = campaignForInfoAdd();

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->assertSet('modal', false)
        ->call('openModal')
        ->assertSet('modal', true);
});

it('creates a campaign information, resets the modal, and dispatches refresh events', function () {
    $campaign = campaignForInfoAdd();

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('openModal')
        ->set('title', 'Local de entrega')
        ->set('content', 'Entregar na secretaria paroquial.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('modal', false)
        ->assertSet('title', null)
        ->assertSet('content', null);

    $info = CampaignInfo::query()->sole();

    expect($info->campaign_id)->toBe($campaign->id)
        ->and($info->title)->toBe('Local de entrega')
        ->and($info->content)->toBe('Entregar na secretaria paroquial.')
        ->and($info->order)->toBe(1);
});

it('requires campaign information fields', function () {
    $campaign = campaignForInfoAdd();

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('openModal')
        ->set('title', '')
        ->set('content', '')
        ->call('save')
        ->assertHasErrors([
            'title' => 'required',
            'content' => 'required',
        ]);
});

it('clears campaign information modal data when closed', function () {
    $campaign = campaignForInfoAdd();

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('openModal')
        ->set('title', 'Local de entrega')
        ->set('content', 'Entregar na secretaria paroquial.')
        ->call('closeModal')
        ->assertSet('modal', false)
        ->assertSet('title', null)
        ->assertSet('content', null);
});

it('refreshes campaign information list after creating an information', function () {
    $campaign = campaignForInfoAdd();

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->assertDontSee('Local de entrega')
        ->call('openModal')
        ->set('title', 'Local de entrega')
        ->set('content', 'Entregar na secretaria paroquial.')
        ->call('save')
        ->assertSee('Local de entrega')
        ->assertSee('Entregar na secretaria paroquial.');
});

it('opens the edit information modal with selected data', function () {
    $campaign = campaignForInfoAdd();
    $info = $campaign->infos()->create([
        'title' => 'Local de entrega',
        'content' => 'Entregar na secretaria paroquial.',
        'order' => 1,
    ]);

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->assertSet('editModal', false)
        ->call('openEditModal', $info->id)
        ->assertSet('editModal', true)
        ->assertSet('editingInfoId', $info->id)
        ->assertSet('editTitle', 'Local de entrega')
        ->assertSet('editContent', 'Entregar na secretaria paroquial.');
});

it('updates a campaign information and resets the edit modal', function () {
    $campaign = campaignForInfoAdd();
    $info = $campaign->infos()->create([
        'title' => 'Local de entrega',
        'content' => 'Entregar na secretaria paroquial.',
        'order' => 1,
    ]);

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('openEditModal', $info->id)
        ->set('editTitle', 'Horário de entrega')
        ->set('editContent', 'Entregar das 8h às 12h.')
        ->call('update')
        ->assertHasNoErrors()
        ->assertSet('editModal', false)
        ->assertSet('editingInfoId', null)
        ->assertSet('editTitle', null)
        ->assertSet('editContent', null)
        ->assertSee('Horário de entrega')
        ->assertSee('Entregar das 8h às 12h.');

    expect($info->refresh()->title)->toBe('Horário de entrega')
        ->and($info->content)->toBe('Entregar das 8h às 12h.');
});

it('requires edit information fields', function () {
    $campaign = campaignForInfoAdd();
    $info = $campaign->infos()->create([
        'title' => 'Local de entrega',
        'content' => 'Entregar na secretaria paroquial.',
        'order' => 1,
    ]);

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('openEditModal', $info->id)
        ->set('editTitle', '')
        ->set('editContent', '')
        ->call('update')
        ->assertHasErrors([
            'editTitle' => 'required',
            'editContent' => 'required',
        ]);
});

it('deletes a campaign information after confirmation', function () {
    $campaign = campaignForInfoAdd();
    $info = $campaign->infos()->create([
        'title' => 'Local de entrega',
        'content' => 'Entregar na secretaria paroquial.',
        'order' => 1,
    ]);

    actingAs($campaign->user);

    Livewire::test('panel.campaign.infos', ['campaignId' => $campaign->id])
        ->call('askToDelete', $info->id)
        ->assertSet('deletingInfoId', $info->id)
        ->call('delete')
        ->assertSet('deletingInfoId', null)
        ->assertDontSee('Local de entrega');

    $this->assertModelMissing($info);
});
