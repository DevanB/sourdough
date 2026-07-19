<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\SwitchTeam;
use App\Models\User;

it('may switch the current team', function (): void {
    $user = User::factory()->create();
    $personal = $user->personalTeam();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    expect($user->fresh()->current_team_id)->toBe($team->id);

    resolve(SwitchTeam::class)->handle($user->fresh(), $personal);

    expect($user->fresh()->current_team_id)->toBe($personal->id);
});

it('throws when the user does not belong to the team', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($other, 'Acme');

    resolve(SwitchTeam::class)->handle($user, $team);
})->throws(RuntimeException::class, 'User does not belong to the team.');
