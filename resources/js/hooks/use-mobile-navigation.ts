export type CleanupFn = () => void;

function clearMobileNavigationPointerEvents(): void {
    document.body.style.removeProperty('pointer-events');
}

export function useMobileNavigation(): CleanupFn {
    return clearMobileNavigationPointerEvents;
}
