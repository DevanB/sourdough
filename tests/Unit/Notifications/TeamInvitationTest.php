<?php

declare(strict_types=1);

use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation as TeamInvitationNotification;

it('is delivered via mail', function (): void {
    $invitation = TeamInvitation::factory()->create([
        'invited_by' => User::factory(),
    ]);

    $notification = new TeamInvitationNotification($invitation);

    expect($notification->via($invitation))->toBe(['mail']);
});

it('builds the invitation mail message', function (): void {
    $invitation = TeamInvitation::factory()->create([
        'invited_by' => User::factory(),
    ]);

    $notification = new TeamInvitationNotification($invitation);
    $mail = $notification->toMail($invitation);

    expect($mail->subject)->toBe("You've been invited to join ".$invitation->team->name)
        ->and($mail->actionText)->toBe('Accept invitation')
        ->and($mail->actionUrl)->toBe(route('team-invitations.show', ['invitation' => $invitation->code]))
        ->and($mail->introLines)->toContain(sprintf('%s has invited you to join the %s team.', $invitation->inviter->name, $invitation->team->name));
});
