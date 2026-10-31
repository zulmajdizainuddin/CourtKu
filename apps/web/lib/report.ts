// The owner's Reports tab (FR-32): which days to show, and how the days become chart bars.
// Dates are plain calendar days ("2026-11-02", business days in Malaysia), so all the date
// maths here is done in UTC on purpose: a day never shifts.

export type ReportRange = { from: string; to: string };

export type RangePreset = "last-7" | "last-30" | "this-month" | "last-month";

export const RANGE_PRESETS: { value: RangePreset; label: string }[] = [
  { value: "last-7", label: "Last 7 days" },
  { value: "last-30", label: "Last 30 days" },
  { value: "this-month", label: "This month" },
  { value: "last-month", label: "Last month" },
];

/** The API allows at most this many days at once (VenueReport::MAX_DAYS). */
export const MAX_REPORT_DAYS = 366;

/** Longer ranges are shown one bar per month instead of one per day, so the bars stay readable. */
export const MAX_DAILY_BARS = 62;

const toDate = (day: string) => new Date(`${day}T00:00:00Z`);
const toDay = (date: Date) => date.toISOString().slice(0, 10);

/** "2026-11-02" + 5 -> "2026-11-07" (and -5 -> "2026-10-28") */
export function addDays(day: string, days: number): string {
  const date = toDate(day);
  date.setUTCDate(date.getUTCDate() + days);
  return toDay(date);
}

/** How many days from "2026-11-01" to "2026-11-30", both included: 30. */
export function daysBetween(from: string, to: string): number {
  return Math.round((toDate(to).getTime() - toDate(from).getTime()) / 86_400_000) + 1;
}

/** The days a preset covers, ending today ("last 7 days" = today and the 6 days before). */
export function presetRange(preset: RangePreset, today: string): ReportRange {
  const [year, month] = today.split("-").map(Number);
  switch (preset) {
    case "last-7":
      return { from: addDays(today, -6), to: today };
    case "last-30":
      return { from: addDays(today, -29), to: today };
    case "this-month":
      return { from: toDay(new Date(Date.UTC(year, month - 1, 1))), to: toDay(new Date(Date.UTC(year, month, 0))) };
    case "last-month":
      return { from: toDay(new Date(Date.UTC(year, month - 2, 1))), to: toDay(new Date(Date.UTC(year, month - 1, 0))) };
  }
}

/** What's wrong with a range the owner typed, in words, or undefined when it's fine. */
export function rangeProblem(range: ReportRange): string | undefined {
  if (!range.from || !range.to) return "Choose a start and an end date.";
  if (range.to < range.from) return "The end date can't be before the start date.";
  if (daysBetween(range.from, range.to) > MAX_REPORT_DAYS) return "Choose at most a year at once.";
  return undefined;
}

export type ReportDay = { date: string; bookings: number; booked_sen: number };

export type ChartBar = {
  key: string;
  /** Under the bar: "2 Nov", or "Nov" for a month. */
  label: string;
  /** In the tooltip and the table: "Mon 2 Nov 2026", or "November 2026". */
  longLabel: string;
  bookings: number;
  booked_sen: number;
};

const shortDay = new Intl.DateTimeFormat("en-MY", { day: "numeric", month: "short", timeZone: "UTC" });
const longDay = new Intl.DateTimeFormat("en-MY", { weekday: "short", day: "numeric", month: "short", year: "numeric", timeZone: "UTC" });
const shortMonth = new Intl.DateTimeFormat("en-MY", { month: "short", timeZone: "UTC" });
const longMonth = new Intl.DateTimeFormat("en-MY", { month: "long", year: "numeric", timeZone: "UTC" });

/** One bar per day, or per month when the range is longer than about two months. */
export function chartBars(days: ReportDay[]): ChartBar[] {
  if (days.length <= MAX_DAILY_BARS) {
    return days.map((day) => ({
      key: day.date,
      label: shortDay.format(toDate(day.date)),
      longLabel: longDay.format(toDate(day.date)),
      bookings: day.bookings,
      booked_sen: day.booked_sen,
    }));
  }

  const months = new Map<string, ChartBar>();
  for (const day of days) {
    const key = day.date.slice(0, 7);
    const bar = months.get(key) ?? {
      key,
      label: shortMonth.format(toDate(`${key}-01`)),
      longLabel: longMonth.format(toDate(`${key}-01`)),
      bookings: 0,
      booked_sen: 0,
    };
    bar.bookings += day.bookings;
    bar.booked_sen += day.booked_sen;
    months.set(key, bar);
  }
  return [...months.values()];
}

/** The top of the chart's scale: a round number at or above the biggest bar (7 -> 10, 23 -> 25, 0 -> 1). */
export function niceMax(value: number): number {
  if (value <= 0) return 1;
  const power = 10 ** Math.floor(Math.log10(value));
  const step = [1, 2, 2.5, 5, 10].find((s) => s * power >= value) ?? 10;
  return step * power;
}
