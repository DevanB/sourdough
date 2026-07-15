<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\User;

it('may create a team', function (): void {
    $user = User::factory()->create();

    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    expect($team->name)->toBe('Acme')
        ->and($team->is_personal)->toBeFalse()
        ->and($user->fresh()->current_team_id)->toBe($team->id)
        ->and($user->ownsTeam($team))->toBeTrue()
        ->and($user->teamRole($team))->toBe(TeamRole::Owner);
});

it('may create a personal team', function (): void {
    $user = User::factory()->withoutPersonalTeam()->create();

    $team = resolve(CreateTeam::class)->handle($user, "{$user->name}'s Team", isPersonal: true);

    expect($team->is_personal)->toBeTrue()
        ->and($user->fresh()->personalTeam()?->is($team))->toBeTrue();
});
