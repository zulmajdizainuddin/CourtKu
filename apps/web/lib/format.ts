// The API sends times in UTC (ISO 8601); people read them in Malaysia time (NFR-12).

const dateFormat = new Intl.DateTimeFormat("en-MY", {
  dateStyle: "medium",
  timeZone: "Asia/Kuala_Lumpur",
});

/** "2026-10-29T16:30:00+00:00" -> "30 Oct 2026" (already the next day in Malaysia) */
export function formatDate(iso: string): string {
  return dateFormat.format(new Date(iso));
}

// Some dates are just calendar days with no time, e.g. a closure from "2027-03-30".
// They must never shift by a day, so they are read and shown in UTC on purpose.
const dayFormat = new Intl.DateTimeFormat("en-MY", { dateStyle: "medium", timeZone: "UTC" });

/** "2027-03-30" -> "30 Mar 2027" */
export function formatDay(day: string): string {
  return dayFormat.format(new Date(`${day}T00:00:00Z`));
}

/** Today's date in Malaysia as "2026-10-30", e.g. the earliest day a closure can start. */
export function todayInMalaysia(now: Date = new Date()): string {
  // en-CA writes dates as YYYY-MM-DD.
  return new Intl.DateTimeFormat("en-CA", { timeZone: "Asia/Kuala_Lumpur" }).format(now);
}
