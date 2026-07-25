<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\SwitchTeam;
use App\Models\User;

it('may switch the current team', function (): void {
    $user = User::factory()->create();
    $personal = $user->personalTeam();
    $this->assertNotNull($personal);
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    expect($freshUser->current_team_id)->toBe($team->id);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    resolve(SwitchTeam::class)->handle($freshUser, $personal);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    expect($freshUser->current_team_id)->toBe($personal->id);
});

it('throws when the user does not belong to the team', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($other, 'Acme');

    resolve(SwitchTeam::class)->handle($user, $team);
})->throws(RuntimeException::class, 'User does not belong to the team.');
