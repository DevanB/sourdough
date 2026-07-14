<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\User;
use Inertia\Support\SessionKey;

it('may update a member role', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->patch(route('teams.members.update', [$team, $member]), [
            'role' => TeamRole::Admin->value,
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Member role updated.'),
            ],
        ]);

    expect($member->fresh()->teamRole($team))->toBe(TeamRole::Admin);
});

it('forbids updating the owner role', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->patch(route('teams.members.update', [$team, $owner]), [
            'role' => TeamRole::Member->value,
        ]);

    $response->assertForbidden();
});

it('may remove a team member', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.members.destroy', [$team, $member]));

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Member removed.'),
            ],
        ]);

    expect($member->fresh()->belongsToTeam($team))->toBeFalse();
});

it('allows a member to leave the team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($member)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.members.destroy', [$team, $member]));

    $response->assertRedirectToRoute('teams.index')
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('You left the team.'),
            ],
        ]);

    expect($member->fresh()->belongsToTeam($team))->toBeFalse();
});

it('forbids the owner from leaving the team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.members.destroy', [$team, $owner]));

    $response->assertForbidden();
});
