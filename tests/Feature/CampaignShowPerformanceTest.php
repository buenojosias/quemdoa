<?php

use App\Livewire\Panel\Campaign\Show;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('does not hydrate campaign relationships on livewire updates', function () {
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'name' => 'Campanha de Alimentos',
        'description' => 'Arrecadacao de alimentos.',
        'confirmation_deadline' => today()->addDays(10)->toDateString(),
        'delivery_deadline' => today()->addDays(20)->toDateString(),
        'is_active' => true,
    ]);

    actingAs($user);

    $component = Livewire::test(Show::class, ['campaign' => $campaign->id])
        ->assertSee('Campanha de Alimentos');

    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $component->call('$refresh')
        ->assertSee('Campanha de Alimentos');

    $executedQueries = collect($queries);

    expect($executedQueries->contains(fn (string $query): bool => str_contains($query, 'from `items`')))->toBeFalse()
        ->and($executedQueries->contains(fn (string $query): bool => str_contains($query, 'from `users`')))->toBeFalse();
});
