export type TeamRole = 'owner' | 'admin' | 'member';

export type UserTeam = {
    id: string;
    name: string;
    isPersonal: boolean;
    role: TeamRole | null;
    roleLabel: string | null;
    isCurrent: boolean;
};

export type TeamPermissions = {
    canUpdateTeam: boolean;
    canDeleteTeam: boolean;
    canAddMember: boolean;
    canUpdateMember: boolean;
    canRemoveMember: boolean;
    canCreateInvitation: boolean;
    canCancelInvitation: boolean;
};

export type TeamMemberItem = {
    id: string;
    name: string;
    email: string;
    role: TeamRole;
    roleLabel: string;
    isOwner: boolean;
    isSelf: boolean;
};

export type PendingInvitation = {
    id: string;
    email: string;
    role: TeamRole;
    roleLabel: string;
    createdAt: string | null;
};

export type AssignableRole = {
    value: Exclude<TeamRole, 'owner'>;
    label: string;
};
