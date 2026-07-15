<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

it('has members', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    expect($team->members)->toHaveCount(2)
        ->and($team->members->pluck('id')->all())->toContain($owner->id, $member->id);
});

it('has memberships', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    expect($team->memberships)->toHaveCount(1)
        ->and($team->memberships->first()->role)->toBe(TeamRole::Owner)
        ->and($team->memberships->first()->user_id)->toBe($owner->id);
});

it('has invitations', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    expect($team->invitations)->toHaveCount(1)
        ->and($team->invitations->first()->is($invitation))->toBeTrue();
});

it('resolves the owner', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    expect($team->owner()?->is($owner))->toBeTrue();
});

it('returns null owner when none exists', function (): void {
    $team = Team::factory()->create();

    expect($team->owner())->toBeNull();
});
