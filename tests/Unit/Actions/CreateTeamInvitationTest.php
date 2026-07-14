<?php

declare(strict_types=1);

use App\Actions\CreateTeamInvitation;
use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;

it('may create a team invitation and notify the recipient', function (): void {
    Notification::fake();

    $owner = User::factory()->create();
    $team = $owner->personalTeam();

    $invitation = resolve(CreateTeamInvitation::class)->handle(
        $team,
        $owner,
        'invitee@example.com',
        TeamRole::Member,
    );

    expect($invitation)->toBeInstanceOf(TeamInvitation::class)
        ->and($invitation->email)->toBe('invitee@example.com')
        ->and($invitation->role)->toBe(TeamRole::Member)
        ->and($invitation->team_id)->toBe($team->id)
        ->and($invitation->invited_by)->toBe($owner->id)
        ->and($invitation->expires_at->toDateTimeString())->toBe(now()->addDays(TeamInvitation::EXPIRY_DAYS)->toDateTimeString())
        ->and($invitation->code)->toHaveLength(64);

    Notification::assertSentOnDemand(TeamInvitationNotification::class);
});
