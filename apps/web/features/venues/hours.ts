"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { OpeningPeriod } from "@/lib/opening-hours";

// Opening hours and closed dates of one venue (FR-07), for the dashboard.

export type SavedPeriod = OpeningPeriod & { closes_next_day: boolean };

export type Closure = {
  id: number;
  starts_on: string; // "2027-03-30"
  ends_on: string;
  reason: string;
};

const hoursKey = (venueId: string) => ["manage", "venues", venueId, "hours"] as const;
const closuresKey = (venueId: string) => ["manage", "venues", venueId, "closures"] as const;

export function useOpeningHours(venueId: string) {
  return useQuery({
    queryKey: hoursKey(venueId),
    queryFn: async () => (await api<{ data: SavedPeriod[] }>(`/api/v1/manage/venues/${venueId}/hours`)).data,
  });
}

/** Saves the whole week at once. A day with no periods is closed. */
export function useSaveOpeningHours(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (periods: OpeningPeriod[]) =>
      (
        await api<{ data: SavedPeriod[] }>(`/api/v1/manage/venues/${venueId}/hours`, {
          method: "PUT",
          body: { periods },
        })
      ).data,
    onSuccess: (saved) => queryClient.setQueryData(hoursKey(venueId), saved),
  });
}

/** Today's and future closures, soonest first. */
export function useClosures(venueId: string) {
  return useQuery({
    queryKey: closuresKey(venueId),
    queryFn: async () => (await api<{ data: Closure[] }>(`/api/v1/manage/venues/${venueId}/closures`)).data,
  });
}

export function useAddClosure(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (input: Omit<Closure, "id">) =>
      api(`/api/v1/manage/venues/${venueId}/closures`, { method: "POST", body: input }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: closuresKey(venueId) }),
  });
}

export function useRemoveClosure(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (closureId: number) =>
      api(`/api/v1/manage/venues/${venueId}/closures/${closureId}`, { method: "DELETE" }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: closuresKey(venueId) }),
  });
}
