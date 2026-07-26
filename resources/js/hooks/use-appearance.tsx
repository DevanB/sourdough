import { useSyncExternalStore } from 'react';

export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system';

export interface UseAppearanceReturn {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
    readonly updateAppearance: (mode: Appearance) => void;
}

const listeners = new Set<() => void>();
let currentAppearance: Appearance = 'system';

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    // oxlint-disable-next-line unicorn/no-document-cookie -- the server reads this cookie to render the correct theme on first paint, so it must be a plain document cookie.
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance => {
    if (typeof window === 'undefined') {
        return 'system';
    }

    const stored = localStorage.getItem('appearance');

    if (stored === 'light' || stored === 'dark' || stored === 'system') {
        return stored;
    }

    return 'system';
};

const isDarkMode = (appearance: Appearance): boolean =>
    appearance === 'dark' || (appearance === 'system' && prefersDark());

const applyTheme = (appearance: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = isDarkMode(appearance);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

// oxlint-disable-next-line promise/prefer-await-to-callbacks -- `useSyncExternalStore` requires a callback-based subscribe function.
const subscribe = (callback: () => void) => {
    listeners.add(callback);

    return () => listeners.delete(callback);
};

const notify = (): void => {
    for (const listener of listeners) {
        listener();
    }
};

function updateAppearance(mode: Appearance): void {
    currentAppearance = mode;

    localStorage.setItem('appearance', mode);
    setCookie('appearance', mode);
    applyTheme(mode);
    notify();
}

const mediaQuery = (): MediaQueryList | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = (): void => {
    applyTheme(currentAppearance);
};

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const storedAppearance = localStorage.getItem('appearance');

    if (storedAppearance === null || storedAppearance === '') {
        localStorage.setItem('appearance', 'system');
        setCookie('appearance', 'system');
    }

    currentAppearance = getStoredAppearance();
    applyTheme(currentAppearance);

    // Set up system theme change listener
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance(): UseAppearanceReturn {
    const appearance: Appearance = useSyncExternalStore(
        subscribe,
        () => currentAppearance,
        () => 'system',
    );

    const resolvedAppearance: ResolvedAppearance = isDarkMode(appearance)
        ? 'dark'
        : 'light';

    return { appearance, resolvedAppearance, updateAppearance } as const;
}
