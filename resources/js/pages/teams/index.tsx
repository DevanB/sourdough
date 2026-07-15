import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import TeamController from '@/actions/App/Http/Controllers/TeamController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, index } from '@/routes/teams';
import type { BreadcrumbItem } from '@/types';
import type { UserTeam } from '@/types/teams';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Teams',
        href: index(),
    },
];

export default function TeamsIndex({ teams }: { teams: UserTeam[] }) {
    const [open, setOpen] = useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teams" />

            <h1 className="sr-only">Teams</h1>

            <SettingsLayout>
                <div className="flex flex-col space-y-6">
                    <div className="flex items-center justify-between">
                        <Heading
                            variant="small"
                            title="Teams"
                            description="Manage your teams and team memberships"
                        />

                        <Dialog open={open} onOpenChange={setOpen}>
                            <DialogTrigger asChild>
                                <Button data-test="teams-new-team-button">
                                    <Plus /> New team
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    key={String(open)}
                                    {...TeamController.store.form()}
                                    className="space-y-6"
                                    onSuccess={() => setOpen(false)}
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Create a new team
                                                </DialogTitle>
                                                <DialogDescription>
                                                    Create a new team to
                                                    collaborate with others.
                                                </DialogDescription>
                                            </DialogHeader>

                                            <div className="grid gap-2">
                                                <Label htmlFor="name">
                                                    Team name
                                                </Label>
                                                <Input
                                                    id="name"
                                                    name="name"
                                                    data-test="create-team-name"
                                                    placeholder="My team"
                                                    required
                                                />
                                                <InputError
                                                    message={errors.name}
                                                />
                                            </div>

                                            <DialogFooter className="gap-2">
                                                <DialogClose asChild>
                                                    <Button variant="secondary">
                                                        Cancel
                                                    </Button>
                                                </DialogClose>

                                                <Button
                                                    type="submit"
                                                    data-test="create-team-submit"
                                                    disabled={processing}
                                                >
                                                    Create team
                                                </Button>
                                            </DialogFooter>
                                        </>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <div className="space-y-3">
                        {teams.map((team) => (
                            <div
                                key={team.id}
                                data-test="team-row"
                                className="flex items-center justify-between rounded-lg border p-4"
                            >
                                <div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {team.name}
                                        </span>
                                        {team.isPersonal ? (
                                            <Badge variant="secondary">
                                                Personal
                                            </Badge>
                                        ) : null}
                                    </div>
                                    <span className="text-sm text-muted-foreground">
                                        {team.roleLabel}
                                    </span>
                                </div>

                                <Button
                                    variant="ghost"
                                    size="sm"
                                    data-test="team-edit-button"
                                    asChild
                                >
                                    <Link href={edit(team.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        ))}

                        {teams.length === 0 ? (
                            <p className="py-8 text-center text-muted-foreground">
                                You don&apos;t belong to any teams yet.
                            </p>
                        ) : null}
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
