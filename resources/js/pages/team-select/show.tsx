import { Head, router } from '@inertiajs/react';
import { Check } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { switchMethod } from '@/routes/teams';
import type { UserTeam } from '@/types/teams';

const selectTeam = (team: UserTeam) => {
    router.put(switchMethod.url(team.id));
};

export default function TeamSelectShow({ teams }: { teams: UserTeam[] }) {
    return (
        <AppLayout>
            <Head title="Select a team" />

            <div className="mx-auto flex max-w-lg flex-col space-y-6 px-4 py-12">
                <Heading
                    title="Select a team"
                    description="Choose which team you want to work in"
                />

                <div className="space-y-3">
                    {teams.map((team) => (
                        <Button
                            key={team.id}
                            variant="outline"
                            data-test="team-select-item"
                            className="h-auto w-full justify-between p-4"
                            onClick={() => selectTeam(team)}
                        >
                            <div className="flex flex-col items-start gap-1">
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
                            {team.isCurrent ? (
                                <Check className="size-4" />
                            ) : null}
                        </Button>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
