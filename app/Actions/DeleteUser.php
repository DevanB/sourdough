<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class DeleteUser
{
    public function __construct(private DeleteTeam $deleteTeam) {}

    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->ownedTeams()
                ->get()
                ->each(fn (Team $team) => $this->deleteTeam->handle($team, $user));

            $user->delete();
        });
    }
}
