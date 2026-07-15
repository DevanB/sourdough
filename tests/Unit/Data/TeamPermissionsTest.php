<?php

declare(strict_types=1);

use App\Data\TeamPermissions;
use App\Enums\TeamRole;

it('builds permissions for a role', function (): void {
    $permissions = TeamPermissions::for(TeamRole::Owner);

    expect($permissions->toArray())->toBe([
        'canUpdateTeam' => true,
        'canDeleteTeam' => true,
        'canAddMember' => true,
        'canUpdateMember' => true,
        'canRemoveMember' => true,
        'canCreateInvitation' => true,
        'canCancelInvitation' => true,
    ]);
});

it('builds permissions for an admin', function (): void {
    $permissions = TeamPermissions::for(TeamRole::Admin);

    expect($permissions->toArray())->toBe([
        'canUpdateTeam' => true,
        'canDeleteTeam' => false,
        'canAddMember' => false,
        'canUpdateMember' => false,
        'canRemoveMember' => false,
        'canCreateInvitation' => true,
        'canCancelInvitation' => true,
    ]);
});

it('builds permissions for a member', function (): void {
    $permissions = TeamPermissions::for(TeamRole::Member);

    expect($permissions->toArray())->toBe([
        'canUpdateTeam' => false,
        'canDeleteTeam' => false,
        'canAddMember' => false,
        'canUpdateMember' => false,
        'canRemoveMember' => false,
        'canCreateInvitation' => false,
        'canCancelInvitation' => false,
    ]);
});

it('builds empty permissions when role is null', function (): void {
    $permissions = TeamPermissions::for(null);

    expect($permissions->toArray())->toBe([
        'canUpdateTeam' => false,
        'canDeleteTeam' => false,
        'canAddMember' => false,
        'canUpdateMember' => false,
        'canRemoveMember' => false,
        'canCreateInvitation' => false,
        'canCancelInvitation' => false,
    ]);
});
