<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

final readonly class UpdateTeamMember
{
    public function handle(Team $team, User $member, TeamRole $role): void
    {
        $team->members()->updateExistingPivot($member->id, [
            'role' => $role,
        ]);
    }
}
