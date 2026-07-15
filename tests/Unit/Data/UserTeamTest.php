<?php

declare(strict_types=1);

use App\Data\UserTeam;

it('converts to an array', function (): void {
    $team = new UserTeam(
        id: 'team-id',
        name: 'Acme',
        isPersonal: false,
        role: 'owner',
        roleLabel: 'Owner',
        isCurrent: true,
    );

    expect($team->toArray())->toBe([
        'id' => 'team-id',
        'name' => 'Acme',
        'isPersonal' => false,
        'role' => 'owner',
        'roleLabel' => 'Owner',
        'isCurrent' => true,
    ]);
});
