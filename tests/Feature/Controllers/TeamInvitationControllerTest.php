<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Support\SessionKey;

it('may invite a member', function (TeamRole $role): void {
    Notification::fake();

    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invitee@example.com',
            'role' => $role->value,
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Invitation sent.'),
            ],
        ]);

    expect($team->invitations()->where('email', 'invitee@example.com')->exists())->toBeTrue();

    Notification::assertSentOnDemand(TeamInvitationNotification::class);
})->with([
    TeamRole::Admin,
    TeamRole::Member,
]);

it('rejects duplicate pending invitations', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invitee@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHasErrors('email');
});

it('rejects invitations for existing members', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create(['email' => 'member@example.com']);

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'member@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHasErrors('email');
});

it('forbids inviting without permission', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $member = User::factory()->create();

    $team->memberships()->create([
        'user_id' => $member->id,
        'role' => TeamRole::Member,
    ]);

    $response = $this->actingAs($member)
        ->fromRoute('teams.edit', $team)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invitee@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertForbidden();
});

it('may cancel an invitation', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.invitations.destroy', [$team, $invitation]));

    $response->assertRedirectToRoute('teams.edit', $team)
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Invitation cancelled.'),
            ],
        ]);

    expect($invitation->fresh())->toBeNull();
});

it('returns not found for invitations scoped to another team', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $otherTeam = resolve(CreateTeam::class)->handle($owner, 'Other');

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $otherTeam->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)
        ->fromRoute('teams.edit', $team)
        ->delete(route('teams.invitations.destroy', [$team, $invitation]));

    $response->assertNotFound();
});
