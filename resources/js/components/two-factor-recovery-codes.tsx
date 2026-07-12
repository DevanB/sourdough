import { Form } from '@inertiajs/react';
import { Eye, EyeOff, LockKeyhole, RefreshCw } from 'lucide-react';
import { useRef, useState } from 'react';
import AlertError from '@/components/alert-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

type Props = {
    recoveryCodesList: string[];
    fetchRecoveryCodes: () => Promise<void>;
    errors: string[];
};

export default function TwoFactorRecoveryCodes({
    recoveryCodesList,
    fetchRecoveryCodes,
    errors,
}: Props) {
    const [codesAreVisible, setCodesAreVisible] = useState<boolean>(false);
    const codesSectionRef = useRef<HTMLUListElement | null>(null);
    const canRegenerateCodes = recoveryCodesList.length > 0 && codesAreVisible;

    async function toggleCodesVisibility(): Promise<void> {
        if (!codesAreVisible && !recoveryCodesList.length) {
            await fetchRecoveryCodes();
        }

        const nextVisible = !codesAreVisible;
        setCodesAreVisible(nextVisible);

        if (nextVisible) {
            requestAnimationFrame(() => {
                codesSectionRef.current?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });
        }
    }

    const RecoveryCodeIconComponent = codesAreVisible ? EyeOff : Eye;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex gap-3">
                    <LockKeyhole className="size-4" aria-hidden="true" />
                    2FA recovery codes
                </CardTitle>
                <CardDescription>
                    Recovery codes let you regain access if you lose your 2FA
                    device. Store them in a secure password manager.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between">
                    <Button
                        type="button"
                        onClick={() => {
                            void toggleCodesVisibility();
                        }}
                        className="w-fit"
                        aria-expanded={codesAreVisible}
                        aria-controls="recovery-codes-section"
                    >
                        <RecoveryCodeIconComponent
                            className="size-4"
                            aria-hidden="true"
                        />
                        {codesAreVisible ? 'Hide' : 'View'} recovery codes
                    </Button>

                    {canRegenerateCodes && (
                        <Form
                            {...regenerateRecoveryCodes.form()}
                            options={{ preserveScroll: true }}
                            onSuccess={fetchRecoveryCodes}
                        >
                            {({ processing }) => (
                                <Button
                                    variant="secondary"
                                    type="submit"
                                    disabled={processing}
                                    aria-describedby="regenerate-warning"
                                >
                                    <RefreshCw /> Regenerate codes
                                </Button>
                            )}
                        </Form>
                    )}
                </div>
                <div
                    id="recovery-codes-section"
                    className={`relative overflow-hidden transition-all duration-300 ${codesAreVisible ? 'h-auto opacity-100' : 'h-0 opacity-0'}`}
                    aria-hidden={!codesAreVisible}
                >
                    <div className="mt-3 space-y-3">
                        {errors?.length ? (
                            <AlertError errors={errors} />
                        ) : (
                            <>
                                <ul
                                    ref={codesSectionRef}
                                    className="grid list-none gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                                    aria-label="Recovery codes"
                                >
                                    {recoveryCodesList.length ? (
                                        recoveryCodesList.map((code) => (
                                            <li
                                                key={code}
                                                className="select-text"
                                            >
                                                {code}
                                            </li>
                                        ))
                                    ) : (
                                        <li
                                            className="list-none space-y-2"
                                            aria-label="Loading recovery codes"
                                        >
                                            {Array.from(
                                                { length: 8 },
                                                (_, index) => (
                                                    <div
                                                        key={`recovery-code-skeleton-${index + 1}`}
                                                        className="h-4 animate-pulse rounded bg-muted-foreground/20"
                                                        aria-hidden="true"
                                                    />
                                                ),
                                            )}
                                        </li>
                                    )}
                                </ul>

                                <div className="text-xs text-muted-foreground select-none">
                                    <p id="regenerate-warning">
                                        Each recovery code can be used once to
                                        access your account and will be removed
                                        after use. If you need more, click{' '}
                                        <span className="font-bold">
                                            Regenerate codes
                                        </span>{' '}
                                        above.
                                    </p>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
