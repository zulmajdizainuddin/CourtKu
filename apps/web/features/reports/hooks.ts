"use client";

import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { ReportDay, ReportRange } from "@/lib/report";

/** GET /api/v1/manage/venues/{id}/reports (VenueReport in the API). Money in sen. */
export type VenueReport = {
  from: string;
  to: string;
  totals: {
    bookings: number;
    hours_booked: number;
    booked_sen: number;
    online_bookings: number;
    counter_bookings: number;
    cancelled: number;
    no_shows: number;
    paid_online_sen: number;
    paid_at_venue_sen: number;
    refunded_sen: number;
    collected_sen: number;
    to_collect_sen: number;
  };
  days: ReportDay[];
  courts: {
    id: number;
    name: string;
    is_active: boolean;
    bookings: number;
    hours_booked: number;
    booked_sen: number;
    /** Hours booked out of hours open, 0-100; null when the venue wasn't open at all. */
    full_percent: number | null;
  }[];
  open_hours_per_court: number;
};

/** The owner's report for a range of days (FR-32). The last report stays on screen while the next loads. */
export function useVenueReport(venueId: string, range: ReportRange, enabled: boolean) {
  return useQuery({
    queryKey: ["venues", venueId, "reports", range.from, range.to],
    queryFn: async () =>
      (await api<{ data: VenueReport }>(`/api/v1/manage/venues/${venueId}/reports?${new URLSearchParams(range)}`)).data,
    enabled,
    placeholderData: keepPreviousData,
  });
}

/** Where the browser downloads the bookings CSV. A plain link: the login cookie goes with it. */
export function bookingsCsvUrl(venueId: string, range: ReportRange): string {
  return `/api/v1/manage/venues/${venueId}/reports/bookings.csv?${new URLSearchParams(range)}`;
}

/** GET /api/v1/admin/stats: platform totals for the admin (FR-33), demo venues left out. */
export type AdminStats = {
  venues: { active: number; pending: number; suspended: number };
  accounts: number;
  bookings: { this_month: number; all_time: number };
  booked_sen: { this_month: number; all_time: number };
};

export function useAdminStats() {
  return useQuery({
    queryKey: ["admin", "stats"],
    queryFn: async () => (await api<{ data: AdminStats }>("/api/v1/admin/stats")).data,
  });
}
