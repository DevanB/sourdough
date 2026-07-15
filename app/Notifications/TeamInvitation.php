<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TeamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeamInvitationModel $invitation) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;
        $inviter = $this->invitation->inviter;

        return (new MailMessage)
            ->subject(__("You've been invited to join :team", ['team' => $team->name]))
            ->line(__(':name has invited you to join the :team team.', [
                'name' => $inviter->name,
                'team' => $team->name,
            ]))
            ->action(__('Accept invitation'), route('team-invitations.show', ['invitation' => $this->invitation->code]));
    }
}
