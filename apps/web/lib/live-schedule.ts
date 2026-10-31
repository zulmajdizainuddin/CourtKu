// The live Schedule tab (FR-14): which bookings just appeared on their own, so staff notice them.

type Booking = { code: string; source: "online" | "walk_in" | "phone" };

/**
 * Bookings players made online that weren't on the schedule before this reload. Walk-ins and phone
 * bookings are left out: staff typed those in themselves. Nothing is new on the first load.
 */
export function newPlayerBookings<T extends Booking>(before: T[] | undefined, now: T[]): T[] {
  if (before === undefined) return [];
  const known = new Set(before.map((b) => b.code));
  return now.filter((b) => b.source === "online" && !known.has(b.code));
}

/** How often the Schedule tab asks whether anything changed. */
export const LIVE_CHECK_MS = 10_000;

/** How long a new booking glows on the schedule. */
export const HIGHLIGHT_MS = 8_000;
