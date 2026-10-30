// Peak and off-peak prices (FR-10): how they are written on screen.
// The API's PriceRule model writes them the same way in its error messages.

import { formatTime, WEEK_ORDER } from "@/lib/opening-hours";

export type PriceRule = {
  id: number;
  /** null = all courts of the venue. */
  court_id: number | null;
  name: string | null;
  /** Business days, 0 = Sunday ... 6 = Saturday. */
  weekdays: number[];
  starts_at: string; // "18:00"
  ends_at: string; // "00:00"
  /** True when it ends after midnight, e.g. 6:00 pm to 2:00 am. The hours after midnight count as the day before (ADR-015). */
  ends_next_day: boolean;
  /** Per hour, in sen. */
  price_sen: number;
  /** The same price as text, e.g. "RM 30.00". */
  price: string;
};

const SHORT_DAY_NAMES = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

/** [1, 2, 3, 4, 5] -> "Mon–Fri", [6, 0] -> "Sat, Sun", all seven -> "Every day". */
export function formatWeekdays(weekdays: number[]): string {
  if (new Set(weekdays).size === 7) return "Every day";

  // Group days that follow each other (Monday first), then write 3 or more as a range.
  const runs: number[][] = [];
  let previous: number | null = null;
  WEEK_ORDER.forEach((day, position) => {
    if (!weekdays.includes(day)) return;
    if (previous !== null && previous === position - 1) runs[runs.length - 1].push(day);
    else runs.push([day]);
    previous = position;
  });

  return runs
    .map((run) => {
      const names = run.map((day) => SHORT_DAY_NAMES[day]);
      return names.length >= 3 ? `${names[0]}–${names[names.length - 1]}` : names.join(", ");
    })
    .join(", ");
}

/** "6:00 pm – midnight", "6:00 pm – 2:00 am (next day)", "8:00 am – 12:00 pm" */
export function formatRuleTimes(rule: Pick<PriceRule, "starts_at" | "ends_at" | "ends_next_day">): string {
  if (rule.ends_next_day && rule.ends_at === "00:00") return `${formatTime(rule.starts_at)} – midnight`;
  return `${formatTime(rule.starts_at)} – ${formatTime(rule.ends_at)}${rule.ends_next_day ? " (next day)" : ""}`;
}
