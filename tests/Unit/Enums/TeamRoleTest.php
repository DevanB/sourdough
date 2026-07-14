<?php

declare(strict_types=1);

use App\Enums\TeamRole;

it('returns a label for each role', function (): void {
    expect(TeamRole::Owner->label())->toBe('Owner')
        ->and(TeamRole::Admin->label())->toBe('Admin')
        ->and(TeamRole::Member->label())->toBe('Member');
});

it('returns permissions for the owner role', function (): void {
    expect(TeamRole::Owner->permissions())->toBe([
        'team:update',
        'team:delete',
        'member:add',
        'member:update',
        'member:remove',
        'invitation:create',
        'invitation:cancel',
    ]);
});

it('returns permissions for the admin role', function (): void {
    expect(TeamRole::Admin->permissions())->toBe([
        'team:update',
        'invitation:create',
        'invitation:cancel',
    ]);
});

it('returns no permissions for the member role', function (): void {
    expect(TeamRole::Member->permissions())->toBe([]);
});

it('checks whether a role has a permission', function (): void {
    expect(TeamRole::Owner->hasPermission('team:delete'))->toBeTrue()
        ->and(TeamRole::Admin->hasPermission('team:delete'))->toBeFalse()
        ->and(TeamRole::Admin->hasPermission('team:update'))->toBeTrue()
        ->and(TeamRole::Member->hasPermission('team:update'))->toBeFalse();
});

it('excludes owner from assignable roles', function (): void {
    expect(TeamRole::assignable())->toBe([
        ['value' => 'admin', 'label' => 'Admin'],
        ['value' => 'member', 'label' => 'Member'],
    ]);
});
