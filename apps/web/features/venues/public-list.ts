"use client";

import { keepPreviousData, useInfiniteQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

/** One venue in the guest list on the home page (VenueCardResource in the API, ADR-031). */
export type VenueCard = {
  name: string;
  slug: string;
  city: string | null;
  state: string;
  is_demo: boolean;
  sports: string[];
  /** The sport most of its courts offer: the drawn court picture when there is no photo. */
  main_sport: string | null;
  /** The cheapest court's normal price per hour, for the chosen sport if any. */
  from_price_sen: number;
  from_price: string;
  cover_url: string | null;
};

export type VenueFilters = { sport: string; state: string; q: string };

type VenuePage = { data: VenueCard[]; meta: { current_page: number; last_page: number; total: number } };

/** "/api/v1/venues?sport=futsal&q=cheras&page=2": empty filters are left out. */
export function venueListPath(filters: VenueFilters, page: number): string {
  const params = new URLSearchParams();
  if (filters.sport) params.set("sport", filters.sport);
  if (filters.state) params.set("state", filters.state);
  if (filters.q.trim()) params.set("q", filters.q.trim());
  if (page > 1) params.set("page", String(page));
  const query = params.toString();
  return `/api/v1/venues${query ? `?${query}` : ""}`;
}

/** The venues guests can book, 24 at a time. The last list stays on screen while new filters load. */
export function useVenueList(filters: VenueFilters) {
  return useInfiniteQuery({
    queryKey: ["public", "venues", filters.sport, filters.state, filters.q.trim()],
    queryFn: ({ pageParam }) => api<VenuePage>(venueListPath(filters, pageParam)),
    initialPageParam: 1,
    getNextPageParam: (last) => (last.meta.current_page < last.meta.last_page ? last.meta.current_page + 1 : undefined),
    placeholderData: keepPreviousData,
  });
}
