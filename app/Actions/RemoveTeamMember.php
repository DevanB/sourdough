<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RemoveTeamMember
{
    public function handle(Team $team, User $member): void
    {
        DB::transaction(function () use ($team, $member): void {
            $team->members()->detach($member->id);

            if ($member->isCurrentTeam($team)) {
                $personalTeam = $member->personalTeam();
                $member->update(['current_team_id' => $personalTeam?->id]);
            }
        });
    }
}
