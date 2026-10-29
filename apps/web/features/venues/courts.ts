"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRefreshPublicPage } from "@/features/venues/refresh-public-page";
import { api } from "@/lib/api";
import type { CourtPayload } from "@/lib/schemas/court";

// The courts of one venue (FR-08), for the dashboard.

export type Court = {
  id: number;
  name: string;
  is_indoor: boolean;
  /** Inactive courts stay here but players can't see or book them. */
  is_active: boolean;
  sort_order: number;
  sports: string[];
};

const courtsKey = (venueId: string) => ["manage", "venues", venueId, "courts"] as const;

/** The courts in the owner's chosen order. */
export function useCourts(venueId: string) {
  return useQuery({
    queryKey: courtsKey(venueId),
    queryFn: async () => (await api<{ data: Court[] }>(`/api/v1/manage/venues/${venueId}/courts`)).data,
  });
}

/** Every change reloads the list, so the order and the details always match the API. */
function useCourtMutation<T>(venueId: string, mutationFn: (input: T) => Promise<unknown>) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn,
    onSuccess: () => {
      refreshPublicPage();
      return queryClient.invalidateQueries({ queryKey: courtsKey(venueId) });
    },
  });
}

export function useAddCourt(venueId: string) {
  return useCourtMutation(venueId, (input: CourtPayload) =>
    api(`/api/v1/manage/venues/${venueId}/courts`, { method: "POST", body: input }),
  );
}

export function useUpdateCourt(venueId: string) {
  return useCourtMutation(venueId, ({ courtId, input }: { courtId: number; input: CourtPayload }) =>
    api(`/api/v1/manage/venues/${venueId}/courts/${courtId}`, { method: "PUT", body: input }),
  );
}

export function useDeleteCourt(venueId: string) {
  return useCourtMutation(venueId, (courtId: number) =>
    api(`/api/v1/manage/venues/${venueId}/courts/${courtId}`, { method: "DELETE" }),
  );
}

/** Saves a new order: every court id of the venue, first to last. */
export function useReorderCourts(venueId: string) {
  return useCourtMutation(venueId, (courtIds: number[]) =>
    api(`/api/v1/manage/venues/${venueId}/courts/order`, { method: "PUT", body: { court_ids: courtIds } }),
  );
}
