import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types/navigation';

export interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export type AppVariant = 'header' | 'sidebar';

export interface FlashToast {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
}

export interface AuthLayoutProps {
    children?: ReactNode;
    name?: string;
    title?: string;
    description?: string;
}
