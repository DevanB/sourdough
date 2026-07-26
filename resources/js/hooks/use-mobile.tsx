import { useSyncExternalStore } from 'react';

const MOBILE_BREAKPOINT = 768;

const mql =
    typeof window === 'undefined'
        ? undefined
        : window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);

// oxlint-disable-next-line promise/prefer-await-to-callbacks -- `useSyncExternalStore` requires a callback-based subscribe function.
function mediaQueryListener(callback: (event: MediaQueryListEvent) => void) {
    mql?.addEventListener('change', callback);

    return () => {
        mql?.removeEventListener('change', callback);
    };
}

function isSmallerThanBreakpoint(): boolean {
    return mql?.matches ?? false;
}

function getServerSnapshot(): boolean {
    return false;
}

export function useIsMobile(): boolean {
    return useSyncExternalStore(
        mediaQueryListener,
        isSmallerThanBreakpoint,
        getServerSnapshot,
    );
}
