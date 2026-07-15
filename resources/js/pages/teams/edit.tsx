import { Form, Head } from '@inertiajs/react';
import TeamController from '@/actions/App/Http/Controllers/TeamController';
import DeleteTeam from '@/components/delete-team';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { InvitationRow } from '@/components/invitation-row';
import { InviteMemberForm } from '@/components/invite-member-form';
import { TeamMemberRow } from '@/components/team-member-row';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, index } from '@/routes/teams';
import { destroy as destroyMember } from '@/routes/teams/members';
import type { BreadcrumbItem } from '@/types';
import type {
    AssignableRole,
    PendingInvitation,
    TeamMemberItem,
    TeamPermissions,
} from '@/types/teams';

type TeamProp = {
    id: string;
    name: string;
    isPersonal: boolean;
};

export default function TeamEdit({
    team,
    members,
    invitations,
    permissions,
    assignableRoles,
    canLeave,
}: {
    team: TeamProp;
    members: TeamMemberItem[];
    invitations: PendingInvitation[];
    permissions: TeamPermissions;
    assignableRoles: AssignableRole[];
    canLeave: boolean;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Teams', href: index() },
        { title: team.name, href: edit(team.id) },
    ];

    const pageTitle = permissions.canUpdateTeam
        ? `Edit ${team.name}`
        : `View ${team.name}`;

    const selfMember = members.find((m) => m.isSelf);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={pageTitle} />
            <h1 className="sr-only">{pageTitle}</h1>

            <SettingsLayout>
                <div className="flex flex-col space-y-10">
                    <div className="space-y-6">
                        {permissions.canUpdateTeam ? (
                            <>
                                <Heading
                                    variant="small"
                                    title="Team settings"
                                    description="Update your team name and settings"
                                />
                                <Form
                                    {...TeamController.update.form(team)}
                                    options={{ preserveScroll: true }}
                                    className="space-y-6"
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="name">
                                                    Team name
                                                </Label>
                                                <Input
                                                    id="name"
                                                    name="name"
                                                    data-test="team-name-input"
                                                    defaultValue={team.name}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.name}
                                                />
                                            </div>
                                            <Button
                                                type="submit"
                                                data-test="team-save-button"
                                                disabled={processing}
                                            >
                                                Save
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </>
                        ) : (
                            <Heading variant="small" title={team.name} />
                        )}
                    </div>

                    <div className="space-y-6">
                        <Heading
                            variant="small"
                            title="Team members"
                            description={
                                permissions.canCreateInvitation
                                    ? 'Manage who belongs to this team'
                                    : ''
                            }
                        />

                        <InviteMemberForm
                            teamId={team.id}
                            permissions={permissions}
                            assignableRoles={assignableRoles}
                        />

                        <div className="space-y-3">
                            {members.map((member) => (
                                <TeamMemberRow
                                    key={member.id}
                                    member={member}
                                    teamId={team.id}
                                    permissions={permissions}
                                    assignableRoles={assignableRoles}
                                />
                            ))}
                        </div>
                    </div>

                    {invitations.length > 0 ? (
                        <div className="space-y-6">
                            <Heading
                                variant="small"
                                title="Pending invitations"
                                description="Invitations that haven't been accepted yet"
                            />
                            <div className="space-y-3">
                                {invitations.map((invitation) => (
                                    <InvitationRow
                                        key={invitation.id}
                                        invitation={invitation}
                                        teamId={team.id}
                                        permissions={permissions}
                                    />
                                ))}
                            </div>
                        </div>
                    ) : null}

                    {canLeave && selfMember ? (
                        <div className="space-y-6">
                            <Heading
                                variant="small"
                                title="Leave team"
                                description="Remove yourself from this team"
                            />
                            <Form
                                {...destroyMember.form([
                                    team.id,
                                    selfMember.id,
                                ])}
                            >
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        data-test="leave-team-button"
                                        disabled={processing}
                                    >
                                        Leave team
                                    </Button>
                                )}
                            </Form>
                        </div>
                    ) : null}

                    {permissions.canDeleteTeam && !team.isPersonal ? (
                        <DeleteTeam team={team} />
                    ) : null}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
