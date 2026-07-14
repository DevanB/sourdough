<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final readonly class CreateTeamInvitation
{
    public function handle(Team $team, User $inviter, string $email, TeamRole $role): TeamInvitation
    {
        $invitation = DB::transaction(function () use ($team, $inviter, $email, $role): TeamInvitation {
            return $team->invitations()->create([
                'email' => $email,
                'role' => $role,
                'code' => Str::random(64),
                'invited_by' => $inviter->id,
                'expires_at' => now()->addDays(TeamInvitation::EXPIRY_DAYS),
            ]);
        });

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        return $invitation;
    }
}
