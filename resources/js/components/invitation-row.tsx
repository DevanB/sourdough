import { Form } from '@inertiajs/react';
import { Mail, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { destroy as destroyInvitation } from '@/routes/teams/invitations';
import type { PendingInvitation, TeamPermissions } from '@/types/teams';

export function InvitationRow({
    invitation,
    teamId,
    permissions,
}: {
    invitation: PendingInvitation;
    teamId: string;
    permissions: TeamPermissions;
}) {
    return (
        <div
            data-test="invitation-row"
            className="flex items-center justify-between rounded-lg border p-4"
        >
            <div className="flex items-center gap-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                    <Mail className="h-5 w-5 text-muted-foreground" />
                </div>
                <div>
                    <div className="font-medium">{invitation.email}</div>
                    <div className="text-sm text-muted-foreground">
                        {invitation.roleLabel}
                    </div>
                </div>
            </div>

            {permissions.canCancelInvitation ? (
                <Form
                    {...destroyInvitation.form([teamId, invitation.id])}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <Button
                            type="submit"
                            variant="ghost"
                            size="sm"
                            data-test="invitation-cancel-button"
                            disabled={processing}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    )}
                </Form>
            ) : null}
        </div>
    );
}
