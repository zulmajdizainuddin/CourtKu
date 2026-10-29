"use client";

import { useInfiniteQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import type { VenueStatus } from "@/features/venues/hooks";
import { api } from "@/lib/api";

/** A venue in the admin's approval list (AdminVenueResource in the API). */
export type AdminVenue = {
  id: number;
  name: string;
  slug: string;
  status: VenueStatus;
  state: string;
  owners: { id: number; name: string; email: string }[];
  created_at: string;
  approved_at: string | null;
  suspended_at: string | null;
};

type AdminVenuePage = { data: AdminVenue[]; meta: { next_cursor: string | null } };

const adminVenuesKey = ["admin", "venues"] as const;

/**
 * Venues for the admin, newest first, 25 at a time.
 * The API pages with a cursor (a bookmark for "where the last page ended") instead of page numbers.
 */
export function useAdminVenues(status: VenueStatus | "all") {
  return useInfiniteQuery({
    queryKey: [...adminVenuesKey, status],
    queryFn: ({ pageParam }) => {
      const params = new URLSearchParams();
      if (status !== "all") params.set("status", status);
      if (pageParam) params.set("cursor", pageParam);
      return api<AdminVenuePage>(`/api/v1/admin/venues?${params}`);
    },
    initialPageParam: null as string | null,
    getNextPageParam: (lastPage) => lastPage.meta.next_cursor,
  });
}

/** Approve (make public) or suspend (hide) a venue. */
export function useChangeVenueStatus() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ venueId, action }: { venueId: number; action: "approve" | "suspend" }) =>
      api(`/api/v1/admin/venues/${venueId}/${action}`, { method: "POST" }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: adminVenuesKey }),
  });
}
