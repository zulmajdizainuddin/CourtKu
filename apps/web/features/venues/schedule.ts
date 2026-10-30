"use client";

import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

// The day schedule for the counter (FR-31), for owners and staff.

/** A booking as the venue sees it (the API's ManageBookingResource). */
export type ScheduleBooking = {
  code: string;
  status: "confirmed" | "checked_in" | "completed" | "no_show" | "cancelled";
  source: "online" | "walk_in" | "phone";
  court_id: number;
  sport: string | null;
  starts_at: string; // "2026-11-06T18:00:00+08:00"
  ends_at: string;
  minutes: number;
  total_sen: number;
  total: string;
  customer: { name: string | null; phone: string | null; email: string | null };
  checked_in_at: string | null;
};

export type DaySchedule = {
  date: string;
  closed_reason: string | null;
  /** The day's half hours, opening to closing, after midnight too (ADR-015). */
  times: { starts_at: string; time: string; next_day: boolean }[];
  courts: { id: number; name: string; is_active: boolean }[];
  bookings: ScheduleBooking[];
};

/** One business day's schedule. Asked again every minute, so new online bookings appear at the counter. */
export function useDaySchedule(venueId: string, date: string) {
  return useQuery({
    queryKey: ["manage", "venues", venueId, "schedule", date],
    queryFn: async () =>
      (await api<{ data: DaySchedule }>(`/api/v1/manage/venues/${venueId}/schedule?date=${date}`)).data,
    refetchInterval: 60_000,
  });
}
