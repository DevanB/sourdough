import { Form, router } from '@inertiajs/react';
import { X } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    destroy as destroyMember,
    update as updateMember,
} from '@/routes/teams/members';
import type {
    AssignableRole,
    TeamMemberItem,
    TeamPermissions,
} from '@/types/teams';

export function TeamMemberRow({
    member,
    teamId,
    permissions,
    assignableRoles,
}: {
    member: TeamMemberItem;
    teamId: string;
    permissions: TeamPermissions;
    assignableRoles: AssignableRole[];
}) {
    return (
        <div
            data-test="member-row"
            className="flex items-center justify-between rounded-lg border p-4"
        >
            <div>
                <div className="font-medium">
                    {member.name}
                    {member.isSelf ? (
                        <span className="text-muted-foreground"> (you)</span>
                    ) : null}
                </div>
                <div className="text-sm text-muted-foreground">
                    {member.email}
                </div>
            </div>

            <div className="flex items-center gap-2">
                {member.isOwner || !permissions.canUpdateMember ? (
                    <Badge variant="secondary">{member.roleLabel}</Badge>
                ) : (
                    <select
                        data-test="member-role-trigger"
                        defaultValue={member.role}
                        aria-label={`Change role for ${member.name}`}
                        className="flex h-8 rounded-md border border-input bg-transparent px-2 text-sm shadow-xs outline-none"
                        onChange={(event) => {
                            router.visit(
                                updateMember.url([teamId, member.id]),
                                {
                                    method: 'patch',
                                    data: { role: event.target.value },
                                    preserveScroll: true,
                                },
                            );
                        }}
                    >
                        {assignableRoles.map((role) => (
                            <option key={role.value} value={role.value}>
                                {role.label}
                            </option>
                        ))}
                    </select>
                )}

                {!member.isOwner &&
                (permissions.canRemoveMember || member.isSelf) ? (
                    <Form
                        {...destroyMember.form([teamId, member.id])}
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="ghost"
                                size="sm"
                                data-test="member-remove-button"
                                disabled={processing}
                            >
                                <X className="h-4 w-4" />
                            </Button>
                        )}
                    </Form>
                ) : null}
            </div>
        </div>
    );
}
