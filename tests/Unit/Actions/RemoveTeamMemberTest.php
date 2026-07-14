<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\RemoveTeamMember;
use App\Enums\TeamRole;
use App\Models\User;

it('may remove a team member', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();
    $personal = $member->personalTeam();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $member->update(['current_team_id' => $team->id]);

    resolve(RemoveTeamMember::class)->handle($team, $member);

    expect($member->fresh()->belongsToTeam($team))->toBeFalse()
        ->and($member->fresh()->current_team_id)->toBe($personal->id);
});

it('does not change current team when removing a member not on that team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();
    $personal = $member->personalTeam();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    expect($member->fresh()->current_team_id)->toBe($personal->id);

    resolve(RemoveTeamMember::class)->handle($team, $member);

    expect($member->fresh()->current_team_id)->toBe($personal->id);
});
