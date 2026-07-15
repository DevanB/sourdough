<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;

final readonly class UpdateTeam
{
    public function handle(Team $team, string $name): Team
    {
        $team->update(['name' => $name]);

        return $team->refresh();
    }
}
