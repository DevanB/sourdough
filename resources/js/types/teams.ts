export type TeamRole = 'owner' | 'admin' | 'member';

export interface UserTeam {
    id: string;
    name: string;
    isPersonal: boolean;
    role: TeamRole | null;
    roleLabel: string | null;
    isCurrent: boolean;
}

export interface TeamPermissions {
    canUpdateTeam: boolean;
    canDeleteTeam: boolean;
    canAddMember: boolean;
    canUpdateMember: boolean;
    canRemoveMember: boolean;
    canCreateInvitation: boolean;
    canCancelInvitation: boolean;
}

export interface TeamMemberItem {
    id: string;
    name: string;
    email: string;
    role: TeamRole;
    roleLabel: string;
    isOwner: boolean;
    isSelf: boolean;
}

export interface PendingInvitation {
    id: string;
    email: string;
    role: TeamRole;
    roleLabel: string;
    createdAt: string | null;
}

export interface AssignableRole {
    value: Exclude<TeamRole, 'owner'>;
    label: string;
}
