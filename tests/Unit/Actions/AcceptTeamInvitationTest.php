<?php

declare(strict_types=1);

use App\Actions\AcceptTeamInvitation;
use App\Actions\CreateTeam;
use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;

it('may accept a team invitation', function (): void {
    $owner = User::factory()->create();
    $team = resolve(CreateTeam::class)->handle($owner, 'Acme');
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invitee@example.com',
        'role' => TeamRole::Admin,
        'invited_by' => $owner->id,
    ]);

    resolve(AcceptTeamInvitation::class)->handle($invitee, $invitation);

    expect($invitee->fresh()->belongsToTeam($team))->toBeTrue()
        ->and($invitee->teamRole($team))->toBe(TeamRole::Admin)
        ->and($invitee->fresh()->current_team_id)->toBe($team->id)
        ->and($invitation->fresh()->isAccepted())->toBeTrue();
});
