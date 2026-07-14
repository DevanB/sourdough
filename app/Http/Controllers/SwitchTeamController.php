<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SwitchTeam;
use App\Http\Requests\SwitchTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final readonly class SwitchTeamController
{
    public function __invoke(
        SwitchTeamRequest $request,
        #[CurrentUser] User $user,
        Team $team,
        SwitchTeam $action,
    ): RedirectResponse {
        $action->handle($user, $team);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Switched team.'),
        ]);

        return to_route('dashboard');
    }
}
