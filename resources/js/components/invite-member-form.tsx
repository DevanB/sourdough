import { Form } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as storeInvitation } from '@/routes/teams/invitations';
import type { AssignableRole, TeamPermissions } from '@/types/teams';

export function InviteMemberForm({
    teamId,
    permissions,
    assignableRoles,
}: {
    teamId: string;
    permissions: TeamPermissions;
    assignableRoles: AssignableRole[];
}) {
    if (!permissions.canCreateInvitation) {
        return null;
    }

    return (
        <Form
            {...storeInvitation.form(teamId)}
            options={{ preserveScroll: true }}
            resetOnSuccess
            className="space-y-4 rounded-lg border p-4"
        >
            {({ errors, processing }) => (
                <>
                    <div className="flex items-center gap-2">
                        <UserPlus className="size-4" />
                        <span className="font-medium">Invite member</span>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                data-test="invite-email-input"
                                required
                            />
                            <InputError message={errors.email} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="role">Role</Label>
                            <select
                                id="role"
                                name="role"
                                data-test="invite-role-select"
                                defaultValue="member"
                                aria-label="Select role for invitation"
                                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                {assignableRoles.map((role) => (
                                    <option key={role.value} value={role.value}>
                                        {role.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.role} />
                        </div>
                    </div>
                    <Button
                        type="submit"
                        data-test="invite-member-button"
                        disabled={processing}
                    >
                        Send invitation
                    </Button>
                </>
            )}
        </Form>
    );
}
