<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\DeleteTeam;
use App\Enums\TeamRole;
use App\Models\User;

it('may delete a team', function (): void {
    $owner = User::factory()->create();
    $personal = $owner->personalTeam();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    resolve(DeleteTeam::class)->handle($team, $owner);

    expect($team->fresh())->toBeNull()
        ->and($owner->fresh()->current_team_id)->toBe($personal->id);
});

it('reassigns other members current team to their personal team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();
    $memberPersonal = $member->personalTeam();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $member->update(['current_team_id' => $team->id]);

    resolve(DeleteTeam::class)->handle($team, $owner);

    expect($member->fresh()->current_team_id)->toBe($memberPersonal->id)
        ->and($owner->fresh()->current_team_id)->toBe($owner->personalTeam()->id);
});
