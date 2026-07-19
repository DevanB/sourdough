<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

it('belongs to a team', function (): void {
    $membership = Membership::factory()->create();

    expect($membership->team)->toBeInstanceOf(Team::class);
});

it('belongs to a user', function (): void {
    $membership = Membership::factory()->create();

    expect($membership->user)->toBeInstanceOf(User::class);
});

it('casts the role to a team role', function (): void {
    $membership = Membership::factory()->create();

    expect($membership->role)->toBe(TeamRole::Member);
});
