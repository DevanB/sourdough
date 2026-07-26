import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import '../css/app.css';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

void createInertiaApp({
    progress: {
        color: '#4B5563',
    },
    strictMode: true,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
});

// This will set light / dark mode on load...
initializeTheme();
