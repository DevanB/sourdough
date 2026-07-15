<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\ResolvePostLoginDestination;
use App\Models\User;

it('redirects to the dashboard when the user has one team', function (): void {
    $user = User::factory()->create();

    $destination = resolve(ResolvePostLoginDestination::class)->handle($user);

    expect($destination)->toBe(route('dashboard', absolute: false));
});

it('redirects to team select when the user has multiple teams', function (): void {
    $user = User::factory()->create();
    resolve(CreateTeam::class)->handle($user, 'Acme');

    $destination = resolve(ResolvePostLoginDestination::class)->handle($user);

    expect($destination)->toBe(route('team-select.show', absolute: false));
});
