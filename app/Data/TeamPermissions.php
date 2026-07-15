<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TeamRole;

final readonly class TeamPermissions
{
    public function __construct(
        public bool $canUpdateTeam,
        public bool $canDeleteTeam,
        public bool $canAddMember,
        public bool $canUpdateMember,
        public bool $canRemoveMember,
        public bool $canCreateInvitation,
        public bool $canCancelInvitation,
    ) {}

    public static function for(?TeamRole $role): self
    {
        return new self(
            canUpdateTeam: $role?->hasPermission('team:update') ?? false,
            canDeleteTeam: $role?->hasPermission('team:delete') ?? false,
            canAddMember: $role?->hasPermission('member:add') ?? false,
            canUpdateMember: $role?->hasPermission('member:update') ?? false,
            canRemoveMember: $role?->hasPermission('member:remove') ?? false,
            canCreateInvitation: $role?->hasPermission('invitation:create') ?? false,
            canCancelInvitation: $role?->hasPermission('invitation:cancel') ?? false,
        );
    }

    /**
     * @return array{canUpdateTeam: bool, canDeleteTeam: bool, canAddMember: bool, canUpdateMember: bool, canRemoveMember: bool, canCreateInvitation: bool, canCancelInvitation: bool}
     */
    public function toArray(): array
    {
        return [
            'canUpdateTeam' => $this->canUpdateTeam,
            'canDeleteTeam' => $this->canDeleteTeam,
            'canAddMember' => $this->canAddMember,
            'canUpdateMember' => $this->canUpdateMember,
            'canRemoveMember' => $this->canRemoveMember,
            'canCreateInvitation' => $this->canCreateInvitation,
            'canCancelInvitation' => $this->canCancelInvitation,
        ];
    }
}
