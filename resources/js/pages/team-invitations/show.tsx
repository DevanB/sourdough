import { Form, Head, Link } from '@inertiajs/react';
import TeamInvitationAcceptanceController from '@/actions/App/Http/Controllers/TeamInvitationAcceptanceController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';

type InvitationProp = {
    code: string;
    email: string;
    role: string;
    roleLabel: string;
    teamName: string;
    inviterName: string;
    isPending: boolean;
    isExpired: boolean;
    isAccepted: boolean;
    emailMatches: boolean;
};

export default function TeamInvitationShow({
    invitation,
}: {
    invitation: InvitationProp;
}) {
    const canAccept =
        invitation.isPending &&
        invitation.emailMatches &&
        !invitation.isAccepted;

    return (
        <AppLayout>
            <Head title="Team invitation" />

            <div className="mx-auto flex max-w-lg flex-col space-y-6 px-4 py-12">
                <Heading
                    title="Team invitation"
                    description={`You've been invited to join ${invitation.teamName}`}
                />

                {invitation.isAccepted ? (
                    <p className="text-muted-foreground">
                        This invitation has already been accepted.
                    </p>
                ) : null}

                {invitation.isExpired ? (
                    <p className="text-muted-foreground">
                        This invitation has expired. Ask the team owner to send
                        a new one.
                    </p>
                ) : null}

                {!invitation.emailMatches && invitation.isPending ? (
                    <p className="text-muted-foreground">
                        This invitation was sent to{' '}
                        <strong>{invitation.email}</strong>. Sign in with that
                        email address to accept it.
                    </p>
                ) : null}

                {canAccept ? (
                    <div className="space-y-4 rounded-lg border p-4">
                        <p>
                            <strong>{invitation.inviterName}</strong> invited
                            you to join <strong>{invitation.teamName}</strong>{' '}
                            as a {invitation.roleLabel.toLowerCase()}.
                        </p>

                        <Form
                            {...TeamInvitationAcceptanceController.store.form(
                                invitation.code,
                            )}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    data-test="accept-invitation-button"
                                    disabled={processing}
                                >
                                    Accept invitation
                                </Button>
                            )}
                        </Form>
                    </div>
                ) : (
                    <Button variant="outline" asChild>
                        <Link href={dashboard()}>Go to dashboard</Link>
                    </Button>
                )}
            </div>
        </AppLayout>
    );
}
