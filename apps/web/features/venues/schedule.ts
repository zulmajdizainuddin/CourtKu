"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import type { BookingStatus } from "@/lib/booking";

// The day schedule for the counter (FR-31), for owners and staff.

/** A booking as the venue sees it (the API's ManageBookingResource). */
export type ScheduleBooking = {
  code: string;
  status: BookingStatus;
  source: "online" | "walk_in" | "phone";
  court_id: number;
  sport: string | null;
  starts_at: string; // "2026-11-06T18:00:00+08:00"
  ends_at: string;
  minutes: number;
  total_sen: number;
  total: string;
  customer: { name: string | null; phone: string | null; email: string | null };
  /** While waiting for online payment: when the held time is freed. */
  hold_expires_at: string | null;
  checked_in_at: string | null;
  /** Paid at the counter (FR-28): when, how and by whom. */
  paid_at: string | null;
  payment_method: PaymentMethod | null;
  paid_by: string | null;
  cancel_reason: string | null;
  /** Which buttons to show, by the same rules the API checks. */
  actions: { check_in: boolean; no_show: boolean; cancel: boolean; mark_paid: boolean };
};

/** How a booking was paid: at the counter (staff choose), or online (set by the payment itself). */
export type PaymentMethod = "cash" | "qr" | "card" | "online";

/** What staff can choose at the counter. */
export const PAYMENT_METHODS: { value: Exclude<PaymentMethod, "online">; label: string }[] = [
  { value: "cash", label: "Cash" },
  { value: "qr", label: "QR / DuitNow" },
  { value: "card", label: "Card" },
];

export const PAYMENT_METHOD_LABELS: Record<PaymentMethod, string> = {
  cash: "Cash",
  qr: "QR / DuitNow",
  card: "Card",
  online: "online payment",
};

/** A time a court is closed for repairs or a private event (FR-21). */
export type ScheduleBlock = {
  id: number;
  court_id: number;
  starts_at: string;
  ends_at: string;
  minutes: number;
  reason: string;
  created_by: string | null;
};

export type DaySchedule = {
  date: string;
  closed_reason: string | null;
  /** The day's half hours, opening to closing, after midnight too (ADR-015). */
  times: { starts_at: string; time: string; next_day: boolean }[];
  courts: { id: number; name: string; is_active: boolean; sports: string[]; bookable: boolean }[];
  bookings: ScheduleBooking[];
  blocks: ScheduleBlock[];
};

const scheduleKey = (venueId: string) => ["manage", "venues", venueId, "schedule"] as const;

/** One business day's schedule. Asked again every minute, so new online bookings appear at the counter. */
export function useDaySchedule(venueId: string, date: string) {
  return useQuery({
    queryKey: [...scheduleKey(venueId), date],
    queryFn: async () =>
      (await api<{ data: DaySchedule }>(`/api/v1/manage/venues/${venueId}/schedule?date=${date}`)).data,
    refetchInterval: 60_000,
  });
}

/** Every change reloads the schedule, so the counter sees it straight away. */
function useScheduleMutation<T, R>(venueId: string, mutationFn: (input: T) => Promise<R>) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: scheduleKey(venueId) }),
  });
}

export type CounterBookingPayload = {
  court_id: number;
  sport: string;
  starts_at: string;
  minutes: number;
  source: "walk_in" | "phone";
  customer_name: string;
  customer_phone: string;
};

/** A walk-in or phone booking (FR-20), paid at the counter. */
export function useAddCounterBooking(venueId: string) {
  return useScheduleMutation(venueId, (input: CounterBookingPayload) =>
    api<{ data: ScheduleBooking }>(`/api/v1/manage/venues/${venueId}/bookings`, { method: "POST", body: input }),
  );
}

export type BlockPayload = { court_ids: number[]; starts_at: string; minutes: number; reason: string };

/** Closes one or more courts for a while (FR-21). All or nothing. */
export function useAddBlock(venueId: string) {
  return useScheduleMutation(venueId, (input: BlockPayload) =>
    api(`/api/v1/manage/venues/${venueId}/blocks`, { method: "POST", body: input }),
  );
}

/** Lifts a block: the time can be booked again. */
export function useLiftBlock(venueId: string) {
  return useScheduleMutation(venueId, (blockId: number) =>
    api(`/api/v1/manage/venues/${venueId}/blocks/${blockId}`, { method: "DELETE" }),
  );
}

export type BookingAction =
  | { action: "check-in" }
  | { action: "no-show" }
  | { action: "paid"; method: Exclude<PaymentMethod, "online"> }
  | { action: "cancel"; reason: string };

/** Check in, no-show, paid or cancel (FR-28), at the counter. */
export function useBookingAction(venueId: string, code: string) {
  return useScheduleMutation(venueId, ({ action, ...body }: BookingAction) =>
    api<{ data: ScheduleBooking }>(`/api/v1/manage/venues/${venueId}/bookings/${encodeURIComponent(code)}/${action}`, {
      method: "POST",
      body,
    }),
  );
}
