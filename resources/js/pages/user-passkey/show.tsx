import { Head } from '@inertiajs/react';
import ManagePasskeys from '@/components/manage-passkeys';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { show } from '@/routes/passkeys';
import type { BreadcrumbItem } from '@/types';
import type { Passkey } from '@/types/auth';

type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Passkeys',
        href: show(),
    },
];

export default function UserPasskey({
    canManagePasskeys = false,
    passkeys = [],
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Passkeys" />
            <SettingsLayout>
                <ManagePasskeys
                    canManagePasskeys={canManagePasskeys}
                    passkeys={passkeys}
                />
            </SettingsLayout>
        </AppLayout>
    );
}
