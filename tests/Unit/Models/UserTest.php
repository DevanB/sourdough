<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->fresh();
    $this->assertNotNull($user);

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'two_factor_confirmed_at',
            'created_at',
            'updated_at',
            'current_team_id',
        ]);
});

test('may have passkeys', function (): void {
    $user = User::factory()->create();

    expect($user->hasPasskeysEnabled())->toBeFalse();

    $user->passkeys()->create([
        'name' => 'MacBook Pro',
        'credential_id' => 'credential-id',
        'credential' => [],
    ]);

    expect($user->hasPasskeysEnabled())->toBeTrue()
        ->and($user->passkeys()->count())->toBe(1);
});

it('belongs to teams', function (): void {
    $user = User::factory()->create();

    $firstTeam = $user->teams->first();
    $this->assertNotNull($firstTeam);

    expect($user->teams)->toHaveCount(1)
        ->and($firstTeam->is_personal)->toBeTrue();
});

it('determines whether it belongs to a team', function (): void {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $this->assertNotNull($team);
    $other = Team::factory()->create();

    expect($user->belongsToTeam($team))->toBeTrue()
        ->and($user->belongsToTeam($other))->toBeFalse();
});

it('determines whether it owns a team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    expect($owner->ownsTeam($team))->toBeTrue()
        ->and($member->ownsTeam($team))->toBeFalse();
});

it('resolves the team role', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Admin,
    ]);

    expect($owner->teamRole($team))->toBe(TeamRole::Owner)
        ->and($member->teamRole($team))->toBe(TeamRole::Admin);
});

it('checks team permissions', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    expect($owner->hasTeamPermission($team, 'team:delete'))->toBeTrue()
        ->and($member->hasTeamPermission($team, 'team:delete'))->toBeFalse();
});

it('resolves the personal team', function (): void {
    $user = User::factory()->create();

    $personalTeam = $user->personalTeam();
    $this->assertNotNull($personalTeam);

    expect($personalTeam->is_personal)->toBeTrue();
});

it('resolves a fallback team excluding the given team', function (): void {
    $user = User::factory()->create();
    $personal = $user->personalTeam();
    $this->assertNotNull($personal);
    $shared = resolve(CreateTeam::class)->handle($user, 'Acme');

    expect($user->fallbackTeam(excluding: $shared)?->is($personal))->toBeTrue()
        ->and($user->fallbackTeam(excluding: $personal)?->is($shared))->toBeTrue();
});

it('maps a team to a user team dto', function (): void {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $this->assertNotNull($team);

    $dto = $user->toUserTeam($team);

    expect($dto->id)->toBe($team->id)
        ->and($dto->name)->toBe($team->name)
        ->and($dto->isPersonal)->toBeTrue()
        ->and($dto->role)->toBe(TeamRole::Owner->value)
        ->and($dto->roleLabel)->toBe('Owner')
        ->and($dto->isCurrent)->toBeTrue();
});

it('lists user teams ordered by name', function (): void {
    $user = User::factory()->withoutPersonalTeam()->create();
    $zebra = resolve(CreateTeam::class)->handle($user, 'Zebra');
    $acme = resolve(CreateTeam::class)->handle($user, 'Acme');

    $teams = $user->userTeams();
    $first = $teams->first();
    $last = $teams->last();
    $this->assertNotNull($first);
    $this->assertNotNull($last);

    expect($teams)->toHaveCount(2)
        ->and($first->name)->toBe('Acme')
        ->and($first->isCurrent)->toBeTrue()
        ->and($last->name)->toBe('Zebra')
        ->and($last->isCurrent)->toBeFalse();
});
