<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final readonly class ResolvePostLoginDestination
{
    public function handle(User $user): string
    {
        if ($user->teams()->count() >= 2) {
            return route('team-select.show', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
