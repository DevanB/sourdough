<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Support\SessionKey;

it('redirects guests to login with the invitation as the intended url', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->fromRoute('home')
        ->get(route('team-invitations.show', $invitation->code))
        ->assertRedirectToRoute('login');

    $invitee = User::factory()->withoutTwoFactor()->create([
        'email' => 'invitee@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'invitee@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirect(route('team-invitations.show', $invitation->code));

    $this->assertAuthenticatedAs($invitee);
});

it('renders the invitation page', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'role' => TeamRole::Member,
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($invitee)
        ->fromRoute('dashboard')
        ->get(route('team-invitations.show', $invitation->code));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('team-invitations/show')
            ->where('invitation.email', 'invitee@example.com')
            ->where('invitation.emailMatches', true)
            ->where('invitation.isPending', true)
            ->where('invitation.teamName', 'Acme'));
});

it('flags email mismatches on the invitation page', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $user = User::factory()->create(['email' => 'other@example.com']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('team-invitations.show', $invitation->code));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('invitation.emailMatches', false));
});

it('flags expired invitations', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($invitee)
        ->fromRoute('dashboard')
        ->get(route('team-invitations.show', $invitation->code));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('invitation.isExpired', true)
            ->where('invitation.isPending', false));
});

it('may accept an invitation', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'INVITEE@example.com',
        'role' => TeamRole::Admin,
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($invitee)
        ->fromRoute('team-invitations.show', $invitation->code)
        ->post(route('team-invitations.accept', $invitation->code));

    $response->assertRedirectToRoute('dashboard')
        ->assertSessionHas(SessionKey::FLASH_DATA, [
            'toast' => [
                'type' => 'success',
                'message' => __('Invitation accepted.'),
            ],
        ]);

    expect($invitee->fresh()->belongsToTeam($team))->toBeTrue()
        ->and($invitee->teamRole($team))->toBe(TeamRole::Admin)
        ->and($invitee->fresh()->current_team_id)->toBe($team->id)
        ->and($invitation->fresh()->isAccepted())->toBeTrue();
});

it('forbids accepting when the email does not match', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $user = User::factory()->create(['email' => 'other@example.com']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('team-invitations.show', $invitation->code)
        ->post(route('team-invitations.accept', $invitation->code));

    $response->assertForbidden();
});

it('forbids accepting an expired invitation', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($invitee)
        ->fromRoute('team-invitations.show', $invitation->code)
        ->post(route('team-invitations.accept', $invitation->code));

    $response->assertForbidden();
});

it('forbids accepting an invitation twice', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->actingAs($invitee)
        ->fromRoute('team-invitations.show', $invitation->code)
        ->post(route('team-invitations.accept', $invitation->code));

    $response->assertForbidden();
});
