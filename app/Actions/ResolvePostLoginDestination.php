<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Support\Features;

final readonly class ResolvePostLoginDestination
{
    public function handle(User $user): string
    {
        if (! Features::teams()) {
            return route('dashboard', absolute: false);
        }

        if ($user->teams()->count() >= 2) {
            return route('team-select.show', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
