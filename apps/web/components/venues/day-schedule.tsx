"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { type ScheduleBooking, useDaySchedule } from "@/features/venues/schedule";
import { endLabel } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { formatDay, todayInMalaysia } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";
import { formatPhone } from "@/lib/phone";
import { sportLabel } from "@/lib/sports";
import { cn } from "cn";

/** "2026-11-06" + 1 -> "2026-11-07" (calendar days, no time zones involved). */
function addDays(day: string, days: number): string {
  return new Date(Date.parse(`${day}T00:00:00Z`) + days * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
}

const STATUS_LABELS: Record<ScheduleBooking["status"], string> = {
  confirmed: "Booked",
  checked_in: "Checked in",
  completed: "Played",
  no_show: "No-show",
  cancelled: "Cancelled",
};

const SOURCE_LABELS: Record<ScheduleBooking["source"], string> = {
  online: "Booked online",
  walk_in: "Walk-in",
  phone: "By phone",
};

/**
 * The day schedule (FR-31) as a timetable: courts across, half hours down, each booking a block
 * over its half hours. Tap a booking to see who booked it and how to reach them.
 */
export function DaySchedule({ venueId }: { venueId: string }) {
  const today = todayInMalaysia();
  const [date, setDate] = useState(today);
  const [selected, setSelected] = useState<string>();
  const schedule = useDaySchedule(venueId, date);
  // Never show a schedule the API just refused, even if the browser still remembers it.
  const data = schedule.isError ? undefined : schedule.data;

  // From the latest date, so tapping an arrow quickly several times moves several days.
  const go = (next: (current: string) => string) => {
    setDate(next);
    setSelected(undefined);
  };
  const booking = data?.bookings.find((b) => b.code === selected);

  return (
    <div className="grid gap-4">
      <div className="flex flex-wrap items-center gap-2">
        <Button variant="outline" className="h-11" aria-label="Previous day" onClick={() => go((d) => addDays(d, -1))}>
          ←
        </Button>
        <Button variant={date === today ? "default" : "outline"} className="h-11" onClick={() => go(() => today)}>
          Today
        </Button>
        <Button variant="outline" className="h-11" aria-label="Next day" onClick={() => go((d) => addDays(d, 1))}>
          →
        </Button>
        <p className="font-medium" aria-live="polite">
          {formatDay(date)}
          {data && ` · ${data.bookings.length} booking${data.bookings.length === 1 ? "" : "s"}`}
        </p>
      </div>

      {schedule.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {schedule.isError && <p className="text-sm text-destructive">Could not load the schedule. Please refresh the page.</p>}
      {data?.closed_reason && <p className="text-sm">Closed: {data.closed_reason}.</p>}
      {data && !data.closed_reason && data.times.length === 0 && (
        <p className="text-sm text-muted-foreground">Closed on this day (no opening hours).</p>
      )}
      {data && data.courts.length === 0 && <p className="text-sm text-muted-foreground">No courts yet.</p>}

      {data && data.times.length > 0 && data.courts.length > 0 && (
        <div className="overflow-x-auto rounded-lg ring-1 ring-foreground/10">
          <div
            className="grid min-w-max text-sm"
            style={{
              gridTemplateColumns: `5.5rem repeat(${data.courts.length}, minmax(9rem, 1fr))`,
              gridTemplateRows: `auto repeat(${data.times.length}, 2.25rem)`,
            }}
          >
            {/* Court names along the top. */}
            <div className="sticky left-0 z-20 border-b bg-background" />
            {data.courts.map((court, column) => (
              <div
                key={court.id}
                className="border-b border-l px-2 py-2 font-medium"
                style={{ gridColumn: column + 2, gridRow: 1 }}
              >
                {court.name}
                {!court.is_active && <span className="ml-1 text-xs font-normal text-muted-foreground">(off)</span>}
              </div>
            ))}

            {/* One row per half hour: the time on the left, an empty cell under each court. */}
            {data.times.map((time, row) => (
              <div key={time.starts_at} className="contents">
                <div
                  className="sticky left-0 z-10 border-t bg-background px-2 pt-0.5 text-xs text-muted-foreground"
                  style={{ gridColumn: 1, gridRow: row + 2 }}
                >
                  {formatTime(time.time)}
                  {time.next_day && <span className="block">(next day)</span>}
                </div>
                {data.courts.map((court, column) => (
                  <div key={court.id} className="border-t border-l" style={{ gridColumn: column + 2, gridRow: row + 2 }} />
                ))}
              </div>
            ))}

            {/* Each booking over its half hours. */}
            {data.bookings.map((b) => {
              const row = data.times.findIndex((t) => t.starts_at === b.starts_at);
              const column = data.courts.findIndex((c) => c.id === b.court_id);
              if (row === -1 || column === -1) return null;
              return (
                <button
                  key={b.code}
                  type="button"
                  aria-pressed={selected === b.code}
                  onClick={() => setSelected(selected === b.code ? undefined : b.code)}
                  className={cn(
                    "z-10 m-0.5 overflow-hidden rounded-md px-2 py-1 text-left text-xs leading-tight focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none",
                    b.status === "no_show"
                      ? "bg-muted text-muted-foreground line-through"
                      : "bg-primary text-primary-foreground",
                    selected === b.code && "ring-2 ring-ring ring-offset-1",
                  )}
                  style={{ gridColumn: column + 2, gridRow: `${row + 2} / span ${b.minutes / 30}` }}
                >
                  <span className="block font-medium">{b.customer.name ?? "Walk-in"}</span>
                  <span className="block">
                    {b.code} · {b.total}
                  </span>
                  {b.status !== "confirmed" && <span className="block">{STATUS_LABELS[b.status]}</span>}
                </button>
              );
            })}
          </div>
        </div>
      )}

      {booking && <BookingDetails booking={booking} courtName={data?.courts.find((c) => c.id === booking.court_id)?.name} />}
    </div>
  );
}

function BookingDetails({ booking, courtName }: { booking: ScheduleBooking; courtName?: string }) {
  const start = booking.starts_at.slice(11, 16);
  return (
    <section aria-label={`Booking ${booking.code}`} className="grid gap-2 rounded-lg p-4 text-sm ring-1 ring-foreground/10">
      <h2 className="font-medium">
        {booking.code} · {STATUS_LABELS[booking.status]}
      </h2>
      <dl className="grid grid-cols-[6rem_1fr] gap-x-3 gap-y-1">
        <dt className="text-muted-foreground">Player</dt>
        <dd>{booking.customer.name ?? "Not given"}</dd>
        {booking.customer.phone && (
          <>
            <dt className="text-muted-foreground">Phone</dt>
            <dd>
              <a href={`tel:${booking.customer.phone}`} className="underline">
                {formatPhone(booking.customer.phone)}
              </a>
            </dd>
          </>
        )}
        {booking.customer.email && (
          <>
            <dt className="text-muted-foreground">Email</dt>
            <dd className="break-all">
              <a href={`mailto:${booking.customer.email}`} className="underline">
                {booking.customer.email}
              </a>
            </dd>
          </>
        )}
        <dt className="text-muted-foreground">Court</dt>
        <dd>
          {courtName}
          {booking.sport && ` · ${sportLabel(booking.sport)}`}
        </dd>
        <dt className="text-muted-foreground">Time</dt>
        <dd>
          {formatTime(start)} – {endLabel(start, booking.minutes)} ({formatDuration(booking.minutes)})
        </dd>
        <dt className="text-muted-foreground">Total</dt>
        <dd>{booking.total}, paid at the counter</dd>
        <dt className="text-muted-foreground">How</dt>
        <dd>{SOURCE_LABELS[booking.source]}</dd>
      </dl>
    </section>
  );
}
