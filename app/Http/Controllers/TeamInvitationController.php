<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CancelTeamInvitation;
use App\Actions\CreateTeamInvitation;
use App\Enums\TeamRole;
use App\Http\Requests\CancelTeamInvitationRequest;
use App\Http\Requests\CreateTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final readonly class TeamInvitationController
{
    public function store(
        CreateTeamInvitationRequest $request,
        #[CurrentUser] User $user,
        Team $team,
        CreateTeamInvitation $action,
    ): RedirectResponse {
        $action->handle(
            $team,
            $user,
            $request->string('email')->value(),
            TeamRole::from($request->string('role')->value()),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Invitation sent.'),
        ]);

        return to_route('teams.edit', $team);
    }

    public function destroy(
        CancelTeamInvitationRequest $request,
        Team $team,
        TeamInvitation $invitation,
        CancelTeamInvitation $action,
    ): RedirectResponse {
        $action->handle($invitation);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Invitation cancelled.'),
        ]);

        return to_route('teams.edit', $team);
    }
}
