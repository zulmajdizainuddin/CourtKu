"use client";

import { useId, useState } from "react";
import { Button } from "@/components/ui/button";
import { useAvailability, useQuote } from "@/features/booking/hooks";
import { ApiError } from "@/lib/api";
import { bookingDates, dateChip, endLabel, lengthOptions, startOptions } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { formatDay, todayInMalaysia } from "@/lib/format";
import { formatRinggit } from "@/lib/money";
import { formatPhone } from "@/lib/phone";
import type { PublicVenue } from "@/lib/public-venue";
import { sportLabel } from "@/lib/sports";
import { cn } from "cn";

/**
 * The booking page (FR-11, FR-12), time first: choose a date, sport and length, then a start
 * time, then one of the courts free for the whole booking. The total comes from the API's quote.
 * Booking itself arrives in M3; until then the player is told to call the venue.
 */
export function BookingPanel({
  slug,
  sports,
  booking,
  phone,
}: {
  slug: string;
  sports: string[];
  booking: PublicVenue["booking"];
  phone: string | null;
}) {
  const dates = bookingDates(todayInMalaysia(), booking.booking_window_days);
  const lengths = lengthOptions(booking);
  const [date, setDate] = useState(dates[0]);
  const [sport, setSport] = useState(sports[0]);
  const [minutes, setMinutes] = useState(lengths[0]);
  const [startsAt, setStartsAt] = useState<string>();
  const [courtId, setCourtId] = useState<number>();

  const availability = useAvailability(slug, date, sport);
  const starts = availability.data ? startOptions(availability.data, minutes) : [];
  // A choice disappears when the list is asked again and that time has started meanwhile.
  const start = starts.find((option) => option.starts_at === startsAt);
  const court = start?.courts.find((c) => c.id === courtId);
  const quote = useQuote(slug, start && court ? { court_id: court.id, starts_at: start.starts_at, minutes } : null);

  // Any earlier choice changes what is free, so the later choices start again.
  const choose = <T,>(set: (value: T) => void) => (value: T) => {
    set(value);
    setStartsAt(undefined);
    setCourtId(undefined);
  };

  return (
    <div className="grid gap-6">
      <Choices
        legend="Date"
        options={dates.map((day, i) => {
          const chip = dateChip(day);
          return { value: day, label: i === 0 ? "Today" : chip.day, detail: chip.date };
        })}
        value={date}
        onChange={choose(setDate)}
        scroll
      />

      {sports.length > 1 && (
        <Choices
          legend="Sport"
          options={sports.map((s) => ({ value: s, label: sportLabel(s) }))}
          value={sport}
          onChange={choose(setSport)}
        />
      )}

      <Choices
        legend="How long"
        options={lengths.map((m) => ({ value: String(m), label: formatDuration(m) }))}
        value={String(minutes)}
        onChange={choose((value: string) => setMinutes(Number(value)))}
      />

      <section aria-labelledby="start-heading" aria-busy={availability.isFetching} className="grid gap-2">
        <h2 id="start-heading" className="text-sm font-medium">
          Start time
        </h2>
        {availability.isPending && <p className="text-sm text-muted-foreground">Finding free times...</p>}
        {availability.isError && (
          <p className="text-sm text-destructive">Could not load the free times. Please refresh the page.</p>
        )}
        {availability.data?.closed_reason && (
          <p className="text-sm">
            Closed on {formatDay(date)}: {availability.data.closed_reason}.
          </p>
        )}
        {availability.data && !availability.data.closed_reason && starts.length === 0 && (
          <p className="text-sm text-muted-foreground">
            No free times for {formatDuration(minutes)} on this day. Try another day or a shorter booking.
          </p>
        )}
        {starts.length > 0 && (
          <Choices
            legend="Start time"
            hideLegend
            options={starts.map((option) => ({ value: option.starts_at, label: option.label }))}
            value={startsAt}
            onChange={(value) => {
              setStartsAt(value);
              setCourtId(undefined);
            }}
          />
        )}
      </section>

      {start && (
        <Choices
          legend={`Free courts, ${start.label} – ${endLabel(start.starts_at.slice(11, 16), minutes)}`}
          options={start.courts.map((c) => ({
            value: String(c.id),
            label: c.name,
            detail: `${c.is_indoor ? "Indoor" : "Outdoor"} · ${formatRinggit(c.price_sen)}`,
          }))}
          value={courtId === undefined ? undefined : String(courtId)}
          onChange={(value) => setCourtId(Number(value))}
        />
      )}

      <section aria-labelledby="summary-heading" className="grid gap-3 rounded-xl p-4 ring-1 ring-foreground/10">
        <h2 id="summary-heading" className="font-medium">
          Your booking
        </h2>
        {start && court ? (
          <div className="grid gap-1 text-sm" aria-live="polite">
            <p>
              {court.name} · {formatDay(date)}, {start.label} – {endLabel(start.starts_at.slice(11, 16), minutes)}
            </p>
            {quote.isPending && <p className="text-muted-foreground">Checking the price...</p>}
            {quote.data && <p className="text-lg font-semibold">Total {quote.data.price}</p>}
            {quote.error && (
              <p className="text-destructive">
                {quote.error instanceof ApiError ? quote.error.message : "Could not check the price. Please try again."}
              </p>
            )}
          </div>
        ) : (
          <p className="text-sm text-muted-foreground">Choose a start time and a court to see the total.</p>
        )}
        <Button className="h-11 justify-self-start px-6" disabled>
          Online booking opens soon
        </Button>
        <p className="text-sm text-muted-foreground">
          {phone ? (
            <>
              To book now, call the venue:{" "}
              <a href={`tel:${phone}`} className="font-medium text-foreground underline">
                {formatPhone(phone)}
              </a>
            </>
          ) : (
            "To book now, contact the venue."
          )}
        </p>
      </section>
    </div>
  );
}

/** A row of buttons where one can be chosen: real radio buttons, so keyboards and screen readers work. */
function Choices({
  legend,
  hideLegend,
  options,
  value,
  onChange,
  scroll,
}: {
  legend: string;
  hideLegend?: boolean;
  options: { value: string; label: string; detail?: string }[];
  value: string | undefined;
  onChange: (value: string) => void;
  scroll?: boolean;
}) {
  const name = useId();
  return (
    <fieldset className="grid min-w-0 gap-2">
      <legend className={cn("mb-2 text-sm font-medium", hideLegend && "sr-only")}>{legend}</legend>
      {/* Dates scroll sideways on phones instead of wrapping into many rows. */}
      <div className={cn("flex gap-2", scroll ? "-mx-4 overflow-x-auto px-4 pb-1" : "flex-wrap")}>
        {options.map((option) => (
          <label
            key={option.value}
            className="flex min-h-11 shrink-0 cursor-pointer flex-col justify-center rounded-lg px-3 py-1.5 text-sm ring-1 ring-foreground/10 has-checked:bg-foreground has-checked:text-background has-focus-visible:ring-2 has-focus-visible:ring-ring"
          >
            <input
              type="radio"
              name={name}
              value={option.value}
              checked={value === option.value}
              onChange={() => onChange(option.value)}
              className="sr-only"
            />
            <span className="font-medium">{option.label}</span>
            {option.detail && <span className="text-xs opacity-80">{option.detail}</span>}
          </label>
        ))}
      </div>
    </fieldset>
  );
}
