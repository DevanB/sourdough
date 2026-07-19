<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class DeleteTeam
{
    public function handle(Team $team, User $actor): void
    {
        DB::transaction(function () use ($team, $actor): void {
            User::query()
                ->where('current_team_id', $team->id)
                ->where('id', '!=', $actor->id)
                ->each(function (User $member): void {
                    $personalTeam = $member->personalTeam();

                    if ($personalTeam instanceof Team) {
                        $member->update(['current_team_id' => $personalTeam->id]);
                    }
                });

            $fallback = $actor->fallbackTeam(excluding: $team);

            $actor->update(['current_team_id' => $fallback?->id]);

            if ($fallback instanceof Team) {
                $actor->setRelation('currentTeam', $fallback);
            } else {
                $actor->unsetRelation('currentTeam');
            }

            $team->delete();
        });
    }
}
