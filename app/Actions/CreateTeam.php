<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateTeam
{
    public function handle(User $owner, string $name, bool $isPersonal = false): Team
    {
        return DB::transaction(function () use ($owner, $name, $isPersonal): Team {
            $team = Team::query()->create([
                'name' => $name,
                'is_personal' => $isPersonal,
            ]);

            $team->memberships()->create([
                'user_id' => $owner->id,
                'role' => TeamRole::Owner,
            ]);

            $owner->update(['current_team_id' => $team->id]);
            $owner->setRelation('currentTeam', $team);

            return $team;
        });
    }
}
