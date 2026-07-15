<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use RuntimeException;

final readonly class SwitchTeam
{
    public function handle(User $user, Team $team): void
    {
        if (! $user->belongsToTeam($team)) {
            throw new RuntimeException('User does not belong to the team.');
        }

        $user->update(['current_team_id' => $team->id]);
        $user->setRelation('currentTeam', $team);
    }
}
