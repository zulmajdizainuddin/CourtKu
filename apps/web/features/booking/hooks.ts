"use client";

import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { Availability, Quote } from "@/lib/booking";

// Free times and prices for the booking page (FR-11, FR-12). Public: no login needed.

/**
 * The free half hours of every court offering the sport on one date.
 * Asked again after 30 seconds or when the player comes back to the tab, so times that
 * have just started (or, from M3, been booked by someone else) disappear.
 */
export function useAvailability(slug: string, date: string, sport: string) {
  return useQuery({
    queryKey: ["public", "venues", slug, "availability", date, sport],
    queryFn: async () => {
      const query = new URLSearchParams({ date, sport });
      return (await api<{ data: Availability }>(`/api/v1/venues/${slug}/availability?${query}`)).data;
    },
    staleTime: 30_000,
  });
}

/**
 * The API's own total for the chosen booking. It checks the booking again (opening hours,
 * notice, length), so the player sees a clear message if the time stopped being bookable.
 * null = nothing chosen yet.
 */
export function useQuote(slug: string, booking: { court_id: number; starts_at: string; minutes: number } | null) {
  return useQuery({
    queryKey: ["public", "venues", slug, "quote", booking],
    queryFn: async () => (await api<{ data: Quote }>(`/api/v1/venues/${slug}/quote`, { method: "POST", body: booking })).data,
    enabled: booking !== null,
    staleTime: 30_000,
    retry: false, // a refusal (e.g. "That time has already started") won't change by asking again
  });
}
