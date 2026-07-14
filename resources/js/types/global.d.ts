import type { Auth } from '@/types/auth';
import type { UserTeam } from '@/types/teams';
import type { FlashToast } from '@/types/ui';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        flashDataType: {
            toast?: FlashToast;
        };
        sharedPageProps: {
            name: string;
            auth: Auth;
            currentTeam: UserTeam | null;
            teams: UserTeam[];
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
