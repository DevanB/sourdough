<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Actions\DeleteTeam;
use App\Enums\TeamRole;
use App\Models\User;

it('may delete a team', function (): void {
    $owner = User::factory()->create();
    $personal = $owner->personalTeam();
    $this->assertNotNull($personal);
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    resolve(DeleteTeam::class)->handle($team, $owner);

    $freshOwner = $owner->fresh();
    $this->assertNotNull($freshOwner);

    expect($team->fresh())->toBeNull()
        ->and($freshOwner->current_team_id)->toBe($personal->id);
});

it('reassigns other members current team to their personal team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();
    $memberPersonal = $member->personalTeam();
    $this->assertNotNull($memberPersonal);

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $member->update(['current_team_id' => $team->id]);

    resolve(DeleteTeam::class)->handle($team, $owner);

    $freshMember = $member->fresh();
    $this->assertNotNull($freshMember);
    $freshOwner = $owner->fresh();
    $this->assertNotNull($freshOwner);
    $ownerPersonal = $freshOwner->personalTeam();
    $this->assertNotNull($ownerPersonal);

    expect($freshMember->current_team_id)->toBe($memberPersonal->id)
        ->and($freshOwner->current_team_id)->toBe($ownerPersonal->id);
});
