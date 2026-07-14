<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateTeam;
use App\Actions\DeleteTeam;
use App\Actions\UpdateTeam;
use App\Enums\TeamRole;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\DeleteTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Membership;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TeamController
{
    public function index(#[CurrentUser] User $user): Response
    {
        return Inertia::render('teams/index', [
            'teams' => $user->userTeams()->map->toArray()->all(),
        ]);
    }

    public function store(CreateTeamRequest $request, #[CurrentUser] User $user, CreateTeam $action): RedirectResponse
    {
        $team = $action->handle($user, $request->string('name')->value());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Team created.'),
        ]);

        return to_route('teams.edit', $team);
    }

    public function edit(#[CurrentUser] User $user, Team $team): Response
    {
        Gate::authorize('view', $team);

        return Inertia::render('teams/edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'isPersonal' => $team->is_personal,
            ],
            'members' => $team->members()->get()->map(function (User $member) use ($user): array {
                /** @var Membership $membership */
                $membership = $member->pivot;

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $membership->role->value,
                    'roleLabel' => $membership->role->label(),
                    'isOwner' => $membership->role === TeamRole::Owner,
                    'isSelf' => $member->is($user),
                ];
            })->all(),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->get()
                ->map(fn (TeamInvitation $invitation): array => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'roleLabel' => $invitation->role->label(),
                    'createdAt' => $invitation->created_at?->toIso8601String(),
                ])->all(),
            'permissions' => $user->toTeamPermissions($team)->toArray(),
            'assignableRoles' => TeamRole::assignable(),
            'canLeave' => ! $user->ownsTeam($team),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action): RedirectResponse
    {
        $action->handle($team, $request->string('name')->value());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Team updated.'),
        ]);

        return to_route('teams.edit', $team);
    }

    public function destroy(DeleteTeamRequest $request, #[CurrentUser] User $user, Team $team, DeleteTeam $action): RedirectResponse
    {
        $action->handle($team, $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Team deleted.'),
        ]);

        return to_route('teams.index');
    }
}
