"use client";

import { useInfiniteQuery, useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { Availability, PlayerBooking, Quote } from "@/lib/booking";

// Free times, prices and booking for the booking page (FR-11, FR-12, FR-15).

const availabilityKey = (slug: string) => ["public", "venues", slug, "availability"] as const;

/**
 * The free half hours of every court offering the sport on one date. Public: no login needed.
 * Asked again after 30 seconds or when the player comes back to the tab, so times that
 * have just started or been booked by someone else disappear.
 */
export function useAvailability(slug: string, date: string, sport: string) {
  return useQuery({
    queryKey: [...availabilityKey(slug), date, sport],
    queryFn: async () => {
      const query = new URLSearchParams({ date, sport });
      return (await api<{ data: Availability }>(`/api/v1/venues/${slug}/availability?${query}`)).data;
    },
    staleTime: 30_000,
  });
}

/**
 * The API's own total for the chosen booking. It checks the booking again (opening hours,
 * notice, length, still free), so the player sees a clear message if the time stopped being
 * bookable. null = nothing chosen yet.
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

export type BookingInput = { court_id: number; sport: string; starts_at: string; minutes: number; pay: "online" | "counter" };

/**
 * Books the chosen court (logged-in players with a verified email).
 * `key` is the Idempotency-Key: the same for every try of one Book press, so a double tap or
 * a retry after a network error gives back the same booking instead of making a second one.
 * Afterwards the free times are asked again, whether it worked or someone else was faster.
 */
export function useBook(slug: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async ({ key, ...input }: BookingInput & { key: string }) =>
      (
        await api<{ data: PlayerBooking }>(`/api/v1/venues/${slug}/bookings`, {
          method: "POST",
          body: input,
          headers: { "Idempotency-Key": key },
        })
      ).data,
    onSettled: () => queryClient.invalidateQueries({ queryKey: availabilityKey(slug) }),
  });
}

/**
 * The player's bookings (FR-30): "upcoming" soonest first, or "past" (with cancelled ones) latest
 * first, 20 at a time. fetchNextPage() asks for the next 20 with the API's cursor.
 */
export function useMyBookings(when: "upcoming" | "past") {
  return useInfiniteQuery({
    queryKey: ["bookings", "mine", when],
    queryFn: async ({ pageParam }) => {
      const query = new URLSearchParams({ when, ...(pageParam ? { cursor: pageParam } : {}) });
      return api<{ data: PlayerBooking[]; meta: { next_cursor: string | null } }>(`/api/v1/me/bookings?${query}`);
    },
    initialPageParam: null as string | null,
    getNextPageParam: (page) => page.meta.next_cursor,
  });
}

/**
 * Cancels one of the player's bookings (FR-24). Afterwards the booking, the lists and the
 * venue's free times are asked again, because the cancelled time is free for others now.
 */
export function useCancelBooking(code: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () => (await api<{ data: PlayerBooking }>(`/api/v1/bookings/${encodeURIComponent(code)}/cancel`, { method: "POST" })).data,
    onSuccess: (booking) => {
      queryClient.setQueryData(["bookings", code], booking);
      return Promise.all([
        queryClient.invalidateQueries({ queryKey: ["bookings", "mine"] }),
        queryClient.invalidateQueries({ queryKey: availabilityKey(booking.venue.slug) }),
      ]);
    },
  });
}

/**
 * One of the player's own bookings, e.g. for the confirmation page. watchPayment: while it waits for
 * payment, ask again every 3 seconds (the API asks Stripe), so "paid" shows as soon as Stripe knows.
 */
export function useBooking(code: string, { watchPayment = false } = {}) {
  return useQuery({
    queryKey: ["bookings", code],
    queryFn: async () => (await api<{ data: PlayerBooking }>(`/api/v1/bookings/${encodeURIComponent(code)}`)).data,
    refetchInterval: (query) => (watchPayment && query.state.data?.status === "pending_payment" ? 3000 : false),
  });
}

/**
 * Pay online (FR-22): gets the payment page for a held booking from CourtKu (the one already open,
 * or a new one) and goes to Stripe's own page. The player pays there, never on CourtKu.
 */
export function usePay() {
  return useMutation({
    mutationFn: async (code: string) =>
      (await api<{ data: { url: string } }>(`/api/v1/bookings/${encodeURIComponent(code)}/pay`, { method: "POST" })).data.url,
    onSuccess: (url) => window.location.assign(url),
  });
}
