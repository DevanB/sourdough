<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\User;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia;

it('renders the teams index', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('teams.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teams/index')
            ->has('teams', 1));
});

it('may create a team', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('teams.index')
        ->post(route('teams.store'), [
            'name' => 'Acme',
        ]);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    $team = $freshUser->teams()->where('name', 'Acme')->first();
    $this->assertNotNull($team);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Team created.'),
            ],
        ]);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    expect($freshUser->current_team_id)->toBe($team->id);
});

it('renders the team edit page for members', function (): void {
    $user = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('teams.index')
        ->get(route('teams.edit', $team));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teams/edit')
            ->where('team.name', 'Acme')
            ->has('members')
            ->has('invitations')
            ->has('permissions')
            ->has('assignableRoles')
            ->has('canLeave'));
});

it('forbids editing a team the user does not belong to', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $outsider = User::factory()->create();

    $response = $this->actingAs($outsider)
        ->fromRoute('teams.index')
        ->get(route('teams.edit', $team));

    $response->assertForbidden();
});

it('may update a team', function (): void {
    $user = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('teams.edit', $team)
        ->patch(route('teams.update', $team), [
            'name' => 'Renamed',
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Team updated.'),
            ],
        ]);

    $freshTeam = $team->fresh();
    $this->assertNotNull($freshTeam);
    expect($freshTeam->name)->toBe('Renamed');
});

it('forbids updating a team without permission', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($member)
        ->fromRoute('teams.edit', $team)
        ->patch(route('teams.update', $team), [
            'name' => 'Renamed',
        ]);

    $response->assertForbidden();
});

it('may delete a team when the name matches', function (): void {
    $user = User::factory()->create();
    $personal = $user->personalTeam();
    $this->assertNotNull($personal);
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.destroy', $team), [
            'name' => 'Acme',
        ]);

    $response->assertRedirectToRoute('teams.index')
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Team deleted.'),
            ],
        ]);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    expect($team->fresh())->toBeNull()
        ->and($freshUser->current_team_id)->toBe($personal->id);
});

it('requires the team name to delete a team', function (): void {
    $user = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.destroy', $team), [
            'name' => 'Wrong',
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHasErrors('name');

    expect($team->fresh())->not->toBeNull();
});

it('forbids deleting a personal team', function (): void {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $this->assertNotNull($team);

    $response = $this->actingAs($user)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response->assertForbidden();
});

it('reassigns other members current team when deleting', function (): void {
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

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.destroy', $team), [
            'name' => 'Acme',
        ]);

    $response->assertRedirectToRoute('teams.index');

    $freshMember = $member->fresh();
    $this->assertNotNull($freshMember);
    expect($freshMember->current_team_id)->toBe($memberPersonal->id);
});

it('may switch teams', function (): void {
    $user = User::factory()->create();
    $personal = $user->personalTeam();
    $this->assertNotNull($personal);
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->put(route('teams.switch', $personal));

    $response->assertRedirectToRoute('dashboard')
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Switched team.'),
            ],
        ]);

    $freshUser = $user->fresh();
    $this->assertNotNull($freshUser);
    expect($freshUser->current_team_id)->toBe($personal->id)
        ->and($team->fresh())->not->toBeNull();
});

it('forbids switching to a team the user does not belong to', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $outsider = User::factory()->create();

    $response = $this->actingAs($outsider)
        ->fromRoute('dashboard')
        ->put(route('teams.switch', $team));

    $response->assertForbidden();
});
