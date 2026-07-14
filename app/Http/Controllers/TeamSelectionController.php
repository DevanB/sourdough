<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TeamSelectionController
{
    public function show(#[CurrentUser] User $user): Response|RedirectResponse
    {
        $teams = $user->userTeams();

        if ($teams->count() < 2) {
            return to_route('dashboard');
        }

        return Inertia::render('team-select/show', [
            'teams' => $teams->map->toArray()->all(),
        ]);
    }
}
