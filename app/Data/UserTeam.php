<?php

declare(strict_types=1);

namespace App\Data;

final readonly class UserTeam
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $isPersonal,
        public ?string $role,
        public ?string $roleLabel,
        public bool $isCurrent = false,
    ) {}

    /**
     * @return array{id: string, name: string, isPersonal: bool, role: string|null, roleLabel: string|null, isCurrent: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isPersonal' => $this->isPersonal,
            'role' => $this->role,
            'roleLabel' => $this->roleLabel,
            'isCurrent' => $this->isCurrent,
        ];
    }
}
