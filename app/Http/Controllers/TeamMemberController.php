<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RemoveTeamMember;
use App\Actions\UpdateTeamMember;
use App\Enums\TeamRole;
use App\Http\Requests\RemoveTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final readonly class TeamMemberController
{
    public function update(
        UpdateTeamMemberRequest $request,
        Team $team,
        User $member,
        UpdateTeamMember $action,
    ): RedirectResponse {
        $action->handle($team, $member, TeamRole::from($request->string('role')->value()));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Member role updated.'),
        ]);

        return to_route('teams.edit', $team);
    }

    public function destroy(
        RemoveTeamMemberRequest $request,
        #[CurrentUser] User $user,
        Team $team,
        User $member,
        RemoveTeamMember $action,
    ): RedirectResponse {
        $action->handle($team, $member);

        $leaving = $user->is($member);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $leaving ? __('You left the team.') : __('Member removed.'),
        ]);

        return $leaving
            ? to_route('teams.index')
            : to_route('teams.edit', $team);
    }
}
