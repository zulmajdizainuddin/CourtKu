"use client";

import { useSyncExternalStore } from "react";

const subscribe = () => () => {};

/**
 * false while the page is drawn on the server and during the browser's first draw (hydration),
 * true right after. Parts that depend on what only the browser knows, e.g. who is logged in,
 * wait for true, so the browser's first draw matches the server's HTML (otherwise React
 * reports a hydration mismatch and draws the whole part again).
 */
export function useHydrated(): boolean {
  return useSyncExternalStore(
    subscribe,
    () => true,
    () => false,
  );
}
