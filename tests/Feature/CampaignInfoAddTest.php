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
