// Opening hours (FR-07): helpers shared by the dashboard forms, and later the public page.
// The overlap rules are the same as App\Support\OpeningHoursRules in the API, which checks them again.

export type OpeningPeriod = {
  /** 0 = Sunday ... 6 = Saturday, the same numbers the API uses. */
  weekday: number;
  opens_at: string; // "08:00"
  closes_at: string; // "02:00"
};

export const DAY_NAMES = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

/** Malaysian calendars start on Monday, so the week is shown Monday to Sunday. */
export const WEEK_ORDER = [1, 2, 3, 4, 5, 6, 0];

/** Every time a period can start or end: on the hour or half hour (ADR-010). "00:00" ... "23:30" */
export const HALF_HOURS = Array.from({ length: 48 }, (_, i) => {
  const hours = String(Math.floor(i / 2)).padStart(2, "0");
  return `${hours}:${i % 2 === 0 ? "00" : "30"}`;
});

function minutes(time: string): number {
  const [hours, mins] = time.split(":").map(Number);
  return hours * 60 + mins;
}

/** Closing at or before the opening time means the next morning (FR-37). 00:00 to 00:00 is open 24 hours. */
export function closesNextDay(opensAt: string, closesAt: string): boolean {
  return minutes(closesAt) <= minutes(opensAt);
}

/** "08:00" -> "8:00 am", "14:30" -> "2:30 pm", "00:00" -> "12:00 am" */
export function formatTime(time: string): string {
  const [hours, mins] = time.split(":").map(Number);
  const suffix = hours < 12 ? "am" : "pm";
  return `${hours % 12 === 0 ? 12 : hours % 12}:${String(mins).padStart(2, "0")} ${suffix}`;
}

/** "8:00 am – 2:00 am (next day)", or "8:00 am – midnight" (clearer than "12:00 am (next day)") */
export function formatPeriod(period: Pick<OpeningPeriod, "opens_at" | "closes_at">): string {
  if (period.closes_at === "00:00" && period.opens_at !== "00:00") {
    return `${formatTime(period.opens_at)} – midnight`;
  }
  if (period.opens_at === "00:00" && period.closes_at === "00:00") return "Open 24 hours";
  const nextDay = closesNextDay(period.opens_at, period.closes_at) ? " (next day)" : "";
  return `${formatTime(period.opens_at)} – ${formatTime(period.closes_at)}${nextDay}`;
}

/** One day in the hours form: open or closed, and its one or two periods. */
export type DayHours = { open: boolean; periods: Pick<OpeningPeriod, "opens_at" | "closes_at">[] };
export type Week = Record<number, DayHours>;

/** What a closed day shows when the owner ticks "open". */
const DEFAULT_PERIOD = { opens_at: "08:00", closes_at: "23:00" };

/** The API's list of periods -> one entry per weekday for the form. */
export function weekFromPeriods(periods: OpeningPeriod[]): Week {
  const week: Week = {};
  for (const weekday of WEEK_ORDER) {
    const day = periods
      .filter((p) => p.weekday === weekday)
      .map(({ opens_at, closes_at }) => ({ opens_at, closes_at }))
      .sort((a, b) => a.opens_at.localeCompare(b.opens_at));
    week[weekday] = day.length > 0 ? { open: true, periods: day } : { open: false, periods: [{ ...DEFAULT_PERIOD }] };
  }
  return week;
}

/** The form's week -> the list the API saves. Closed days send nothing. */
export function periodsFromWeek(week: Week): OpeningPeriod[] {
  return WEEK_ORDER.flatMap((weekday) =>
    week[weekday].open ? week[weekday].periods.map((p) => ({ weekday, ...p })) : [],
  );
}

const DAY = 1440;
const WEEK = 7 * DAY;

/** When the period opens and closes, in minutes since Sunday 00:00. */
function span(period: OpeningPeriod): [number, number] {
  const dayStart = period.weekday * DAY;
  const nextDay = closesNextDay(period.opens_at, period.closes_at) ? DAY : 0;
  return [dayStart + minutes(period.opens_at), dayStart + minutes(period.closes_at) + nextDay];
}

function describe(period: OpeningPeriod): string {
  return `${DAY_NAMES[period.weekday]} ${formatPeriod(period)}`;
}

/**
 * What is wrong with this week, in plain words: more than two periods on a day (ADR-016),
 * or two periods that overlap, including a late night running into the next morning.
 */
export function openingHoursProblems(periods: OpeningPeriod[]): string[] {
  const problems: string[] = [];

  for (const weekday of WEEK_ORDER) {
    if (periods.filter((p) => p.weekday === weekday).length > 2) {
      problems.push(`${DAY_NAMES[weekday]} can have at most two opening periods.`);
    }
  }

  const spans = periods.map(span);
  spans.forEach(([start, end], i) => {
    spans.forEach(([otherStart, otherEnd], j) => {
      if (j <= i) return;
      // Also one week later and earlier, so Saturday night meets Sunday morning.
      const overlaps = [0, WEEK, -WEEK].some((shift) => start < otherEnd + shift && otherStart + shift < end);
      if (overlaps) problems.push(`${describe(periods[j])} overlaps ${describe(periods[i])}.`);
    });
  });

  return problems;
}
