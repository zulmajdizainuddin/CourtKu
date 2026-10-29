"use client";

import { useQueryClient } from "@tanstack/react-query";
import type { ManageVenue } from "@/features/venues/hooks";
import { refreshPublicVenuePage } from "@/features/venues/public-page-actions";

/**
 * After a dashboard save, refreshes the venue's public page so the owner sees the change
 * straight away. Best effort: if it fails, the page still updates within a minute.
 */
export function useRefreshPublicPage(venueId: string) {
  const queryClient = useQueryClient();
  return (slug?: string) => {
    const venueSlug = slug ?? queryClient.getQueryData<ManageVenue>(["manage", "venues", venueId])?.slug;
    if (venueSlug) refreshPublicVenuePage(venueSlug).catch(() => {});
  };
}
