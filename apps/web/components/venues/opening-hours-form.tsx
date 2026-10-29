"use client";

import { useState } from "react";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type SavedPeriod, useSaveOpeningHours } from "@/features/venues/hours";
import { ApiError } from "@/lib/api";
import {
  closesNextDay,
  DAY_NAMES,
  type DayHours,
  formatPeriod,
  formatTime,
  HALF_HOURS,
  openingHoursProblems,
  periodsFromWeek,
  type Week,
  WEEK_ORDER,
  weekFromPeriods,
} from "@/lib/opening-hours";

/**
 * The owner's weekly opening hours (FR-07): each day open or closed, with one or two
 * periods (ADR-016), which may close after midnight (FR-37). Saves the whole week at once.
 */
export function OpeningHoursForm({ venueId, saved }: { venueId: string; saved: SavedPeriod[] }) {
  const save = useSaveOpeningHours(venueId);
  const [week, setWeek] = useState<Week>(() => weekFromPeriods(saved));
  const [problems, setProblems] = useState<string[]>([]);
  const [done, setDone] = useState(false);

  const changeDay = (weekday: number, change: (day: DayHours) => DayHours) => {
    setDone(false);
    setWeek((current) => ({ ...current, [weekday]: change(current[weekday]) }));
  };

  const copyMondayToAll = () => {
    setDone(false);
    setWeek((current) => {
      const monday = current[1];
      return Object.fromEntries(
        WEEK_ORDER.map((weekday) => [weekday, { open: monday.open, periods: monday.periods.map((p) => ({ ...p })) }]),
      );
    });
  };

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setDone(false);
    const periods = periodsFromWeek(week);

    // Same rules as the API, so most mistakes show without waiting for the server.
    const found = openingHoursProblems(periods);
    setProblems(found);
    if (found.length > 0) return;

    try {
      setWeek(weekFromPeriods(await save.mutateAsync(periods)));
      setDone(true);
    } catch (err) {
      if (err instanceof ApiError) {
        const fieldMessages = Object.values(err.fields).flat();
        setProblems(fieldMessages.length > 0 ? fieldMessages : [err.message]);
      } else {
        setProblems(["Could not reach CourtKu. Check your connection and try again."]);
      }
    }
  };

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <div>
        {WEEK_ORDER.map((weekday) => (
          <DayRow key={weekday} weekday={weekday} day={week[weekday]} onChange={(change) => changeDay(weekday, change)} />
        ))}
      </div>

      <Button type="button" variant="outline" className="justify-self-start" onClick={copyMondayToAll}>
        Copy Monday to every day
      </Button>

      {problems.length > 0 && (
        <ul role="alert" className="grid gap-1 rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">
          {problems.map((problem) => (
            <li key={problem}>{problem}</li>
          ))}
        </ul>
      )}
      {done && <FormAlert tone="success" message="Opening hours saved." />}

      <Button type="submit" size="lg" className="h-11 justify-self-start px-6" disabled={save.isPending}>
        {save.isPending ? "Saving..." : "Save opening hours"}
      </Button>
    </form>
  );
}

function DayRow({
  weekday,
  day,
  onChange,
}: {
  weekday: number;
  day: DayHours;
  onChange: (change: (day: DayHours) => DayHours) => void;
}) {
  const name = DAY_NAMES[weekday];

  const setPeriod = (index: number, field: "opens_at" | "closes_at", value: string) =>
    onChange((d) => ({ ...d, periods: d.periods.map((p, i) => (i === index ? { ...p, [field]: value } : p)) }));

  // A second period, e.g. closed for Friday prayers: 8:00 am – 12:30 pm, then 2:30 pm until the usual closing.
  const addBreak = () =>
    onChange((d) => {
      const [first] = d.periods;
      const canSplit = first.opens_at < "12:30" && (closesNextDay(first.opens_at, first.closes_at) || first.closes_at > "14:30");
      return {
        ...d,
        periods: canSplit
          ? [{ ...first, closes_at: "12:30" }, { opens_at: "14:30", closes_at: first.closes_at }]
          : [first, { opens_at: "18:00", closes_at: "23:00" }],
      };
    });

  return (
    <fieldset className="grid gap-2 border-b py-3 last:border-b-0">
      <legend className="sr-only">{name}</legend>
      <div className="flex min-h-11 items-center justify-between gap-3">
        <label className="flex cursor-pointer items-center gap-3 font-medium">
          <input
            type="checkbox"
            checked={day.open}
            onChange={(e) => onChange((d) => ({ ...d, open: e.target.checked }))}
            className="size-4 accent-foreground"
          />
          {name}
        </label>
        {!day.open && <span className="text-sm text-muted-foreground">Closed</span>}
      </div>

      {day.open &&
        day.periods.map((period, index) => (
          // Phones: opens – closes side by side in two equal columns. Wider screens: one row.
          <div
            key={index}
            className="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 sm:flex sm:flex-wrap sm:pl-7"
          >
            <TimeSelect
              label={`${name}${index === 1 ? " second period" : ""} opens`}
              value={period.opens_at}
              onChange={(value) => setPeriod(index, "opens_at", value)}
            />
            <span aria-hidden="true">–</span>
            <TimeSelect
              label={`${name}${index === 1 ? " second period" : ""} closes`}
              value={period.closes_at}
              opensAt={period.opens_at}
              onChange={(value) => setPeriod(index, "closes_at", value)}
            />
            {index === 1 && (
              <Button
                type="button"
                variant="ghost"
                className="col-span-3 justify-self-start"
                aria-label={`Remove ${name}'s second period`}
                onClick={() => onChange((d) => ({ ...d, periods: [d.periods[0]] }))}
              >
                Remove
              </Button>
            )}
          </div>
        ))}

      {day.open && day.periods.length === 1 && (
        <Button type="button" variant="link" className="justify-self-start px-0 sm:ml-7" onClick={addBreak}>
          + Add a break (e.g. Friday prayers)
        </Button>
      )}
      {/* In words, for two periods or a late night: on a phone, "(next day)" may not fit in the dropdown. */}
      {day.open && (day.periods.length === 2 || day.periods.some((p) => closesNextDay(p.opens_at, p.closes_at))) && (
        <p className="text-xs text-muted-foreground sm:pl-7">
          Open {day.periods.map(formatPeriod).join(", then ")}.
        </p>
      )}
    </fieldset>
  );
}

/** Half-hour times as a dropdown. For a closing time, "(next day)" marks times after midnight. */
function TimeSelect({
  label,
  value,
  opensAt,
  onChange,
}: {
  label: string;
  value: string;
  opensAt?: string;
  onChange: (value: string) => void;
}) {
  return (
    <select
      aria-label={label}
      value={value}
      onChange={(e) => onChange(e.target.value)}
      className="h-11 w-full min-w-0 rounded-lg border border-input bg-transparent px-2.5 text-base outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 sm:w-auto dark:bg-input/30"
    >
      {HALF_HOURS.map((time) => (
        <option key={time} value={time}>
          {formatTime(time)}
          {opensAt !== undefined && closesNextDay(opensAt, time) ? " (next day)" : ""}
        </option>
      ))}
    </select>
  );
}

/** Staff see the hours without changing them. */
export function OpeningHoursView({ saved }: { saved: SavedPeriod[] }) {
  const week = weekFromPeriods(saved);
  return (
    <dl className="grid gap-2 text-sm sm:grid-cols-[8rem_1fr]">
      {WEEK_ORDER.map((weekday) => (
        <div key={weekday} className="contents">
          <dt className="font-medium">{DAY_NAMES[weekday]}</dt>
          <dd className="text-muted-foreground">
            {week[weekday].open ? week[weekday].periods.map(formatPeriod).join(", then ") : "Closed"}
          </dd>
        </div>
      ))}
    </dl>
  );
}
