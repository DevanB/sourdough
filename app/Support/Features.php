<?php

declare(strict_types=1);

namespace App\Support;

final readonly class Features
{
    public static function teams(): bool
    {
        return (bool) config('features.teams');
    }
}
