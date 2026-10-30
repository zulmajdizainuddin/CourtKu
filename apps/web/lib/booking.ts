// The booking page /v/[slug]/book (FR-11, FR-12): turning the API's free half hours into
// start times, free courts and totals. Plain functions, so they are easy to test.

import { durationOptions } from "@/lib/duration";
import { formatTime } from "@/lib/opening-hours";
import { SPORTS } from "@/lib/sports";

/** One half hour players can book (ADR-010). */
export type AvailabilityCell = {
  /** The real time, e.g. "2026-11-07T00:30:00+08:00" for 00:30 after a late Friday. */
  starts_at: string;
  time: string; // "00:30"
  /** This half hour's price in sen. */
  price_sen: number;
  available: boolean;
};

/** The API's answer to GET /api/v1/venues/{slug}/availability. */
export type Availability = {
  date: string;
  closed_reason: string | null;
  slot_minutes: number;
  min_minutes: number;
  max_minutes: number;
  courts: { id: number; name: string; is_indoor: boolean; sports: string[]; cells: AvailabilityCell[] }[];
};

/** The API's answer to POST /api/v1/venues/{slug}/quote. */
export type Quote = {
  court_id: number;
  date: string;
  starts_at: string;
  ends_at: string;
  minutes: number;
  price_sen: number;
  price: string; // "RM 30.00"
};

/** The API's PlayerBookingResource: a booking as its player sees it. */
export type PlayerBooking = {
  code: string; // "CK-7F3K2"
  status: "confirmed" | "checked_in" | "completed" | "no_show" | "cancelled";
  venue: { name: string; slug: string; phone: string | null };
  court: { id: number; name: string };
  sport: string | null;
  starts_at: string; // "2026-11-06T18:00:00+08:00"
  ends_at: string;
  business_date: string;
  minutes: number;
  total_sen: number;
  total: string; // "RM 20.00"
  /** In M3 every booking is paid at the venue. */
  pay_at_venue: boolean;
};

/** A start time players can choose, with every court free for the whole booking from then. */
export type StartOption = {
  starts_at: string;
  label: string; // "6:00 pm", or "12:30 am (next day)"
  courts: { id: number; name: string; is_indoor: boolean; price_sen: number }[];
};

const HALF_HOUR_MS = 30 * 60 * 1000;

/** The dates players can choose: today and the venue's "days ahead", e.g. 14 -> 15 dates. */
export function bookingDates(today: string, windowDays: number): string[] {
  const first = new Date(`${today}T00:00:00Z`);
  return Array.from({ length: windowDays + 1 }, (_, i) =>
    new Date(first.getTime() + i * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
  );
}

/**
 * Just after midnight, last night's late hours (e.g. Friday until 2 am) can still be booked,
 * and they belong to yesterday's business day (ADR-015). Before 6 am in Malaysia, offer
 * yesterday too. A venue that closed at midnight simply shows no free times for it.
 */
export function lastNightDate(today: string, hourInMalaysia: number): string | null {
  if (hourInMalaysia >= 6) return null;
  return new Date(Date.parse(`${today}T00:00:00Z`) - 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
}

// Calendar days, shown in UTC on purpose so they never shift by a day (like formatDay).
const chipDay = new Intl.DateTimeFormat("en-MY", { weekday: "short", timeZone: "UTC" });
const chipDate = new Intl.DateTimeFormat("en-MY", { day: "numeric", month: "short", timeZone: "UTC" });

/** "2026-11-06" -> { day: "Fri", date: "6 Nov" } for the date buttons. */
export function dateChip(day: string): { day: string; date: string } {
  const date = new Date(`${day}T00:00:00Z`);
  return { day: chipDay.format(date), date: chipDate.format(date) };
}

/** The booking lengths the venue allows: its shortest to longest, in whole slots (FR-13). */
export function lengthOptions(booking: { slot_minutes: number; min_minutes: number; max_minutes: number }): number[] {
  return durationOptions(booking.slot_minutes, booking.max_minutes).filter((minutes) => minutes >= booking.min_minutes);
}

/** The sports a venue's courts offer, in the usual order (badminton, futsal, pickleball). */
export function venueSports(courts: { sports: string[] }[]): string[] {
  const offered = new Set(courts.flatMap((court) => court.sports));
  return SPORTS.map((sport) => sport.value).filter((sport) => offered.has(sport));
}

/** "18:00" -> "6:00 pm"; a half hour after midnight on the chosen date's night gets "(next day)". */
export function cellLabel(cell: Pick<AvailabilityCell, "starts_at" | "time">, date: string): string {
  return `${formatTime(cell.time)}${cell.starts_at.slice(0, 10) !== date ? " (next day)" : ""}`;
}

/** When a booking ends: "18:00" + 120 -> "8:00 pm", "23:00" + 60 -> "midnight". */
export function endLabel(startTime: string, minutes: number): string {
  const [hours, mins] = startTime.split(":").map(Number);
  const end = (hours * 60 + mins + minutes) % 1440;
  if (end === 0) return "midnight";
  return formatTime(`${String(Math.floor(end / 60)).padStart(2, "0")}:${String(end % 60).padStart(2, "0")}`);
}

/**
 * Every start time with at least one court free for the whole booking, earliest first.
 * A court is free from a half hour when it and the next ones (minutes / 30 in all) are
 * available and follow each other with no gap, e.g. not across a Friday prayer break.
 */
export function startOptions(availability: Availability, minutes: number): StartOption[] {
  const cellsNeeded = minutes / 30;
  const options = new Map<string, StartOption>();

  for (const court of availability.courts) {
    court.cells.forEach((first, index) => {
      const cells = court.cells.slice(index, index + cellsNeeded);
      const unbroken =
        cells.length === cellsNeeded &&
        cells.every(
          (cell, i) =>
            cell.available && Date.parse(cell.starts_at) === Date.parse(first.starts_at) + i * HALF_HOUR_MS,
        );
      if (!unbroken) return;

      const option = options.get(first.starts_at) ?? {
        starts_at: first.starts_at,
        label: cellLabel(first, availability.date),
        courts: [],
      };
      option.courts.push({
        id: court.id,
        name: court.name,
        is_indoor: court.is_indoor,
        price_sen: cells.reduce((total, cell) => total + cell.price_sen, 0),
      });
      options.set(first.starts_at, option);
    });
  }

  return [...options.values()].sort((a, b) => Date.parse(a.starts_at) - Date.parse(b.starts_at));
}
