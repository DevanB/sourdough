import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import TeamController from '@/actions/App/Http/Controllers/TeamController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function DeleteTeam({
    team,
}: {
    team: { id: string; name: string };
}) {
    const nameInput = useRef<HTMLInputElement>(null);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="Delete team"
                description="Permanently delete this team and all of its resources"
            />
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">Warning</p>
                    <p className="text-sm">
                        Please proceed with caution, this cannot be undone.
                    </p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button
                            variant="destructive"
                            data-test="delete-team-button"
                        >
                            Delete team
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            Are you sure you want to delete this team?
                        </DialogTitle>
                        <DialogDescription>
                            Once this team is deleted, all of its resources and
                            data will also be permanently deleted. Please type
                            the team name to confirm.
                        </DialogDescription>

                        <Form
                            {...TeamController.destroy.form(team)}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => nameInput.current?.focus()}
                            resetOnSuccess
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            Type &ldquo;{team.name}&rdquo; to
                                            confirm
                                        </Label>

                                        <Input
                                            id="name"
                                            name="name"
                                            ref={nameInput}
                                            placeholder={team.name}
                                            autoComplete="off"
                                        />

                                        <InputError message={errors.name} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button
                                                variant="secondary"
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                Cancel
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            variant="destructive"
                                            disabled={processing}
                                            asChild
                                        >
                                            <button
                                                type="submit"
                                                data-test="confirm-delete-team-button"
                                            >
                                                Delete team
                                            </button>
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
