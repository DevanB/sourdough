<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\UpdateTeamMember;
use App\Enums\TeamRole;
use App\Models\User;

it('may update a team member role', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    resolve(UpdateTeamMember::class)->handle($team, $member, TeamRole::Admin);

    expect($member->fresh()->teamRole($team))->toBe(TeamRole::Admin);
});
