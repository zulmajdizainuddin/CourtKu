"use client";

import { useSearchParams } from "next/navigation";
import { useState } from "react";
import { FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { BlockForm, CounterBookingForm } from "@/components/venues/schedule-forms";
import {
  type BookingAction,
  PAYMENT_METHODS,
  type ScheduleBlock,
  type ScheduleBooking,
  useBookingAction,
  useDaySchedule,
  useLiftBlock,
} from "@/features/venues/schedule";
import { ApiError } from "@/lib/api";
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
  pending_payment: "Waiting for payment",
  confirmed: "Booked",
  checked_in: "Checked in",
  completed: "Played",
  no_show: "No-show",
  cancelled: "Cancelled",
  expired: "Not paid in time",
};

const SOURCE_LABELS: Record<ScheduleBooking["source"], string> = {
  online: "Booked online",
  walk_in: "Walk-in",
  phone: "By phone",
};

/**
 * The day schedule (FR-31) as a timetable: courts across, half hours down, each booking a tile
 * over its half hours, blocked times in grey. Tap one to see the details. From here staff also
 * add walk-in and phone bookings (FR-20) and block times (FR-21).
 */
export function DaySchedule({
  venueId,
  rules,
}: {
  venueId: string;
  rules: { slot_minutes: number; min_minutes: number; max_minutes: number };
}) {
  const today = todayInMalaysia();
  // ?date=2026-11-06 opens that day, e.g. from the list of bookings a settings change would strand.
  // Read here, inside VenuePage's <Suspense>, as Next.js requires for the address's ?query.
  const linked = useSearchParams().get("date");
  const [date, setDate] = useState(linked && /^\d{4}-\d{2}-\d{2}$/.test(linked) ? linked : today);
  // A booking code, or "block-12" for a block.
  const [selected, setSelected] = useState<string>();
  const [adding, setAdding] = useState<"booking" | "block">();
  const schedule = useDaySchedule(venueId, date);
  // Never show a schedule the API just refused, even if the browser still remembers it.
  const data = schedule.isError ? undefined : schedule.data;

  // From the latest date, so tapping an arrow quickly several times moves several days.
  const go = (next: (current: string) => string) => {
    setDate(next);
    setSelected(undefined);
    setAdding(undefined);
  };
  const booking = data?.bookings.find((b) => b.code === selected);
  const block = data?.blocks.find((b) => `block-${b.id}` === selected);
  const canAdd = data !== undefined && data.times.length > 0 && data.courts.length > 0;

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

      {canAdd && !adding && (
        <div className="flex flex-wrap gap-2">
          <Button className="h-11 px-4" onClick={() => setAdding("booking")}>
            Add booking
          </Button>
          <Button variant="outline" className="h-11 px-4" onClick={() => setAdding("block")}>
            Block time
          </Button>
        </div>
      )}
      {data && adding && (
        <div className="rounded-lg p-4 ring-1 ring-foreground/10">
          {adding === "booking" ? (
            <CounterBookingForm venueId={venueId} schedule={data} rules={rules} onDone={() => setAdding(undefined)} />
          ) : (
            <BlockForm venueId={venueId} schedule={data} onDone={() => setAdding(undefined)} />
          )}
        </div>
      )}

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
                      : b.status === "checked_in"
                        ? "bg-emerald-700 text-white"
                        : b.status === "pending_payment"
                          ? "bg-amber-100 text-amber-950 ring-1 ring-amber-500 ring-inset dark:bg-amber-950 dark:text-amber-100"
                          : "bg-primary text-primary-foreground",
                    selected === b.code && "ring-2 ring-ring ring-offset-1",
                  )}
                  style={{ gridColumn: column + 2, gridRow: `${row + 2} / span ${b.minutes / 30}` }}
                >
                  <span className="block font-medium">{b.customer.name ?? "Walk-in"}</span>
                  <span className="block">
                    {b.code} · {b.total}
                  </span>
                  <span className="block">
                    {b.status !== "confirmed" && `${STATUS_LABELS[b.status]} · `}
                    {b.paid_at ? "Paid" : "Not paid yet"}
                  </span>
                </button>
              );
            })}

            {/* Blocked times, in grey with their reason. */}
            {data.blocks.map((b) => {
              const row = data.times.findIndex((t) => t.starts_at === b.starts_at);
              const column = data.courts.findIndex((c) => c.id === b.court_id);
              if (row === -1 || column === -1) return null;
              const key = `block-${b.id}`;
              return (
                <button
                  key={key}
                  type="button"
                  aria-pressed={selected === key}
                  onClick={() => setSelected(selected === key ? undefined : key)}
                  className={cn(
                    "z-10 m-0.5 overflow-hidden rounded-md border border-dashed border-foreground/30 bg-muted px-2 py-1 text-left text-xs leading-tight text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none",
                    selected === key && "ring-2 ring-ring ring-offset-1",
                  )}
                  style={{ gridColumn: column + 2, gridRow: `${row + 2} / span ${b.minutes / 30}` }}
                >
                  <span className="block font-medium">Blocked</span>
                  <span className="block">{b.reason}</span>
                </button>
              );
            })}
          </div>
        </div>
      )}

      {booking && (
        <BookingDetails
          venueId={venueId}
          booking={booking}
          courtName={data?.courts.find((c) => c.id === booking.court_id)?.name}
        />
      )}
      {block && (
        <BlockDetails
          venueId={venueId}
          block={block}
          courtName={data?.courts.find((c) => c.id === block.court_id)?.name}
          onLifted={() => setSelected(undefined)}
        />
      )}
    </div>
  );
}

function BookingDetails({
  venueId,
  booking,
  courtName,
}: {
  venueId: string;
  booking: ScheduleBooking;
  courtName?: string;
}) {
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
        <dd>
          {booking.total}
          {booking.paid_at
            ? `, paid by ${PAYMENT_METHODS.find((m) => m.value === booking.payment_method)?.label ?? "the counter"} at ${formatTime(booking.paid_at.slice(11, 16))}${booking.paid_by ? ` (${booking.paid_by})` : ""}`
            : ", not paid yet"}
        </dd>
        <dt className="text-muted-foreground">How</dt>
        <dd>{SOURCE_LABELS[booking.source]}</dd>
        {booking.checked_in_at && (
          <>
            <dt className="text-muted-foreground">Arrived</dt>
            <dd>{formatTime(booking.checked_in_at.slice(11, 16))}</dd>
          </>
        )}
      </dl>
      <BookingActions venueId={venueId} booking={booking} />
    </section>
  );
}

/** A blocked time, and lifting it so the time can be booked again. */
function BlockDetails({
  venueId,
  block,
  courtName,
  onLifted,
}: {
  venueId: string;
  block: ScheduleBlock;
  courtName?: string;
  onLifted: () => void;
}) {
  const lift = useLiftBlock(venueId);
  const start = block.starts_at.slice(11, 16);

  const onLift = () => {
    if (window.confirm(`Lift this block on ${courtName ?? "this court"}? Players can book the time again.`)) {
      lift.mutate(block.id, { onSuccess: onLifted });
    }
  };

  return (
    <section aria-label="Blocked time" className="grid gap-2 rounded-lg p-4 text-sm ring-1 ring-foreground/10">
      <h2 className="font-medium">Blocked: {block.reason}</h2>
      <dl className="grid grid-cols-[6rem_1fr] gap-x-3 gap-y-1">
        <dt className="text-muted-foreground">Court</dt>
        <dd>{courtName}</dd>
        <dt className="text-muted-foreground">Time</dt>
        <dd>
          {formatTime(start)} – {endLabel(start, block.minutes)} ({formatDuration(block.minutes)})
        </dd>
        {block.created_by && (
          <>
            <dt className="text-muted-foreground">By</dt>
            <dd>{block.created_by}</dd>
          </>
        )}
      </dl>
      <Button variant="outline" className="h-11 justify-self-start px-6" disabled={lift.isPending} onClick={onLift}>
        {lift.isPending ? "Lifting..." : "Lift block"}
      </Button>
      {lift.error && (
        <p className="text-sm text-destructive" role="alert">
          {lift.error instanceof ApiError ? lift.error.message : "Could not reach CourtKu. Check your connection and try again."}
        </p>
      )}
    </section>
  );
}

/**
 * The counter buttons for one booking (FR-28): check in, no-show, paid (how) and cancel (why).
 * Each button shows only when the API would allow it (booking.actions).
 */
function BookingActions({ venueId, booking }: { venueId: string; booking: ScheduleBooking }) {
  const act = useBookingAction(venueId, booking.code);
  const [cancelling, setCancelling] = useState(false);
  const [reason, setReason] = useState("");
  const { actions } = booking;

  const run = (action: BookingAction, question?: string) => {
    if (question && !window.confirm(question)) return;
    act.mutate(action, { onSuccess: () => setCancelling(false) });
  };

  if (!actions.check_in && !actions.no_show && !actions.cancel && !actions.mark_paid) return null;

  return (
    <div className="grid gap-3 border-t pt-3">
      <div className="flex flex-wrap gap-2">
        {actions.check_in && (
          <Button className="h-11 px-4" disabled={act.isPending} onClick={() => run({ action: "check-in" })}>
            Check in
          </Button>
        )}
        {actions.no_show && (
          <Button
            variant="outline"
            className="h-11 px-4"
            disabled={act.isPending}
            onClick={() => run({ action: "no-show" }, `Mark ${booking.code} as a no-show? The time stays taken.`)}
          >
            No-show
          </Button>
        )}
        {actions.cancel && !cancelling && (
          <Button variant="outline" className="h-11 px-4 text-destructive" disabled={act.isPending} onClick={() => setCancelling(true)}>
            Cancel booking
          </Button>
        )}
      </div>

      {actions.mark_paid && (
        <div className="grid gap-2">
          <p className="text-sm font-medium">Mark paid at the counter</p>
          <div className="flex flex-wrap gap-2">
            {PAYMENT_METHODS.map((m) => (
              <Button
                key={m.value}
                variant="outline"
                className="h-11 px-4"
                disabled={act.isPending}
                onClick={() => run({ action: "paid", method: m.value })}
              >
                {m.label}
              </Button>
            ))}
          </div>
        </div>
      )}

      {cancelling && (
        <form
          className="grid gap-2"
          onSubmit={(e) => {
            e.preventDefault();
            run({ action: "cancel", reason });
          }}
        >
          <FormField label="Why cancel?" name="cancel_reason" placeholder="Rain, or the player asked" value={reason} onChange={(e) => setReason(e.target.value)} />
          <div className="flex flex-wrap gap-2">
            <Button type="submit" variant="outline" className="h-11 px-4 text-destructive" disabled={act.isPending}>
              Cancel this booking
            </Button>
            <Button type="button" variant="ghost" className="h-11" onClick={() => setCancelling(false)}>
              Keep it
            </Button>
          </div>
        </form>
      )}

      {act.error && (
        <p className="text-sm text-destructive" role="alert">
          {act.error instanceof ApiError
            ? (Object.values(act.error.fields).flat()[0] ?? act.error.message)
            : "Could not reach CourtKu. Check your connection and try again."}
        </p>
      )}
    </div>
  );
}
