<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('stores a temporary dismissal decision when closed', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('panel.dashboard.whatsapp-alert')
        ->call('dismiss')
        ->assertSet('hidden', true)
        ->assertDispatched('dashboard-whatsapp-alert-dismissed');

    expect(session('dashboard_whatsapp_alert_dismissed'))->toBeTrue();
});

it('does not render on the dashboard when the alert was dismissed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['dashboard_whatsapp_alert_dismissed' => true])
        ->get(route('panel.dashboard'));

    $response
        ->assertSuccessful()
        ->assertDontSee('Adicione e confirme seu WhatsApp');
});

it('sends a confirmation code to a new whatsapp number', function () {
    config([
        'services.evolution.base_url' => 'https://evolution.test',
        'services.evolution.api_key' => 'evolution-api-key',
        'services.evolution.instance' => 'teste-josias',
        'services.evolution.country_code' => '55',
    ]);

    Http::fake([
        'https://evolution.test/message/sendText/teste-josias' => Http::response(['sent' => true]),
    ]);

    $user = User::factory()->create([
        'name' => 'Maria Silva',
        'whatsapp' => null,
        'whatsapp_verified_at' => null,
    ]);

    Livewire::actingAs($user)
        ->test('panel.dashboard.whatsapp-alert')
        ->call('openModal')
        ->set('whatsapp', '(11) 99999-9999')
        ->call('sendCode')
        ->assertHasNoErrors()
        ->assertSet('codeSent', true)
        ->assertSet('editingWhatsapp', false);

    $user->refresh();

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request->url() === 'https://evolution.test/message/sendText/teste-josias'
            && $request->hasHeader('apikey', 'evolution-api-key')
            && $request['number'] === '5511999999999'
            && str_contains($request['text'], 'Olá, Maria Silva!')
            && str_contains($request['text'], (string) $user->whatsapp_confirmation_code);
    });

    expect($user->whatsapp)->toBe('11999999999')
        ->and($user->whatsapp_confirmation_code)->toHaveLength(5)
        ->and($user->whatsapp_verified_at)->toBeNull();
});

it('allows confirming with an existing code without sending a new message', function () {
    Http::fake();

    $user = User::factory()->create([
        'whatsapp' => '11999999999',
        'whatsapp_confirmation_code' => '12345',
        'whatsapp_verified_at' => null,
    ]);

    Livewire::actingAs($user)
        ->test('panel.dashboard.whatsapp-alert')
        ->call('openModal')
        ->assertSee('Já tenho um código')
        ->call('useExistingCode')
        ->assertHasNoErrors()
        ->assertSet('codeSent', true)
        ->assertSet('editingWhatsapp', false);

    Http::assertNothingSent();
});

it('confirms the whatsapp code and removes the alert decision', function () {
    $user = User::factory()->create([
        'whatsapp' => '11999999999',
        'whatsapp_confirmation_code' => '12345',
        'whatsapp_verified_at' => null,
    ]);

    session()->put('dashboard_whatsapp_alert_dismissed', true);

    Livewire::actingAs($user)
        ->test('panel.dashboard.whatsapp-alert')
        ->set('pin', '12345')
        ->call('confirmCode')
        ->assertHasNoErrors()
        ->assertSet('hidden', true)
        ->assertSet('modal', false)
        ->assertDispatched('dashboard-whatsapp-confirmed');

    $user->refresh();

    expect($user->whatsapp_verified_at)->not->toBeNull()
        ->and($user->whatsapp_confirmation_code)->toBeNull()
        ->and(session()->has('dashboard_whatsapp_alert_dismissed'))->toBeFalse();
});
