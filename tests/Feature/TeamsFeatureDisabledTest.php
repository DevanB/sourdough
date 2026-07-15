<?php

declare(strict_types=1);

use App\Actions\CreateTeam;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    config(['features.teams' => false]);
});

it('returns not found for team routes', function (): void {
    $user = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($user, 'Acme');
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertNotFound();

    $this->put(route('teams.switch', $team))
        ->assertNotFound();

    $this->post(route('teams.invitations.store', $team), [
        'email' => 'invitee@example.com',
        'role' => 'member',
    ])->assertNotFound();

    $this->get(route('team-invitations.show', $invitation->code))
        ->assertNotFound();

    $this->get(route('team-select.show'))
        ->assertNotFound();
});

it('redirects users with multiple teams to the dashboard after login', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticatedAs($user);
});

it('shares the disabled feature state without team data', function (): void {
    $user = User::factory()->create();

    resolve(CreateTeam::class)->handle($user, 'Acme');

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('features.teams', false)
            ->where('currentTeam', null)
            ->has('teams', 0));
});

it('creates a personal team during registration', function (): void {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirectToRoute('dashboard');

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $personalTeam = $user->personalTeam();

    expect($personalTeam)->not->toBeNull()
        ->and($personalTeam?->is_personal)->toBeTrue()
        ->and($user->current_team_id)->toBe($personalTeam?->id);
});
