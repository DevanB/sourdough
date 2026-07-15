<?php

declare(strict_types=1);

namespace App\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role): bool => $role !== self::Owner)
            ->map(fn (self $role): array => ['value' => $role->value, 'label' => $role->label()])
            ->values()
            ->all();
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => [
                'team:update',
                'team:delete',
                'member:add',
                'member:update',
                'member:remove',
                'invitation:create',
                'invitation:cancel',
            ],
            self::Admin => [
                'team:update',
                'invitation:create',
                'invitation:cancel',
            ],
            self::Member => [],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
