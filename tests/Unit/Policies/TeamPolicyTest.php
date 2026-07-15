<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\User;
use App\Policies\TeamPolicy;

it('allows members to view a team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $outsider = User::factory()->create();

    $policy = new TeamPolicy;

    expect($policy->view($owner, $team))->toBeTrue()
        ->and($policy->view($outsider, $team))->toBeFalse();
});

it('allows users with team update permission to update', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $admin = User::factory()->create();
    $member = User::factory()->create();

    $team->memberships()->create(['user_id' => $admin->id, 'role' => TeamRole::Admin]);
    $team->memberships()->create(['user_id' => $member->id, 'role' => TeamRole::Member]);

    $policy = new TeamPolicy;

    expect($policy->update($owner, $team))->toBeTrue()
        ->and($policy->update($admin, $team))->toBeTrue()
        ->and($policy->update($member, $team))->toBeFalse();
});

it('allows owners to add members', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $admin = User::factory()->create();

    $team->memberships()->create(['user_id' => $admin->id, 'role' => TeamRole::Admin]);

    $policy = new TeamPolicy;

    expect($policy->addMember($owner, $team))->toBeTrue()
        ->and($policy->addMember($admin, $team))->toBeFalse();
});

it('allows owners and admins to invite and cancel invitations', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $admin = User::factory()->create();
    $member = User::factory()->create();

    $team->memberships()->create(['user_id' => $admin->id, 'role' => TeamRole::Admin]);
    $team->memberships()->create(['user_id' => $member->id, 'role' => TeamRole::Member]);

    $policy = new TeamPolicy;

    expect($policy->inviteMember($owner, $team))->toBeTrue()
        ->and($policy->inviteMember($admin, $team))->toBeTrue()
        ->and($policy->inviteMember($member, $team))->toBeFalse()
        ->and($policy->cancelInvitation($owner, $team))->toBeTrue()
        ->and($policy->cancelInvitation($admin, $team))->toBeTrue()
        ->and($policy->cancelInvitation($member, $team))->toBeFalse();
});

it('prevents deleting personal teams and allows owners to delete shared teams', function (): void {
    $owner = User::factory()->create();
    $personalTeam = $owner->personalTeam();
    $sharedTeam = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $policy = new TeamPolicy;

    expect($policy->delete($owner, $personalTeam))->toBeFalse()
        ->and($policy->delete($owner, $sharedTeam))->toBeTrue();
});

it('allows updating members except the owner', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create(['user_id' => $member->id, 'role' => TeamRole::Member]);

    $policy = new TeamPolicy;

    expect($policy->updateMember($owner, $team, $member))->toBeTrue()
        ->and($policy->updateMember($owner, $team, $owner))->toBeFalse();
});

it('allows removing members or self-leaving but not removing the owner', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();
    $other = User::factory()->create();

    $team->memberships()->create(['user_id' => $member->id, 'role' => TeamRole::Member]);
    $team->memberships()->create(['user_id' => $other->id, 'role' => TeamRole::Member]);

    $policy = new TeamPolicy;

    expect($policy->removeMember($owner, $team, $member))->toBeTrue()
        ->and($policy->removeMember($member, $team, $member))->toBeTrue()
        ->and($policy->removeMember($other, $team, $member))->toBeFalse()
        ->and($policy->removeMember($owner, $team, $owner))->toBeFalse();
});
