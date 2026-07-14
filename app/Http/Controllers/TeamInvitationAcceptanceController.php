<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AcceptTeamInvitation;
use App\Http\Requests\AcceptTeamInvitationRequest;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TeamInvitationAcceptanceController
{
    public function show(#[CurrentUser] User $user, TeamInvitation $invitation): Response
    {
        $emailMatches = strcasecmp($invitation->email, $user->email) === 0;

        return Inertia::render('team-invitations/show', [
            'invitation' => [
                'code' => $invitation->code,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'roleLabel' => $invitation->role->label(),
                'teamName' => $invitation->team->name,
                'inviterName' => $invitation->inviter->name,
                'isPending' => $invitation->isPending(),
                'isExpired' => $invitation->isExpired(),
                'isAccepted' => $invitation->isAccepted(),
                'emailMatches' => $emailMatches,
            ],
        ]);
    }

    public function store(
        AcceptTeamInvitationRequest $request,
        #[CurrentUser] User $user,
        TeamInvitation $invitation,
        AcceptTeamInvitation $action,
    ): RedirectResponse {
        $action->handle($user, $invitation);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Invitation accepted.'),
        ]);

        return to_route('dashboard');
    }
}
