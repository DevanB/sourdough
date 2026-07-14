<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\UpdateTeam;
use App\Models\User;

it('may update a team', function (): void {
    $user = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $updated = resolve(UpdateTeam::class)->handle($team, 'Renamed');

    expect($updated->name)->toBe('Renamed')
        ->and($team->fresh()->name)->toBe('Renamed');
});
