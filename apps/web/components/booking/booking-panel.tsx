"use client";

import { useRouter } from "next/navigation";
import { useId, useState } from "react";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { useMe } from "@/features/auth/hooks";
import { type BookingInput, useAvailability, useBook, usePay, useQuote } from "@/features/booking/hooks";
import { ApiError } from "@/lib/api";
import { bookingDates, dateChip, endLabel, lastNightDate, lengthOptions, startOptions } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { formatDay, hourInMalaysia, todayInMalaysia } from "@/lib/format";
import { formatRinggit } from "@/lib/money";
import { formatPhone } from "@/lib/phone";
import { DemoNotice } from "@/components/booking/demo-notice";
import type { PublicVenue } from "@/lib/public-venue";
import { sportLabel } from "@/lib/sports";
import { useHydrated } from "@/lib/use-hydrated";
import { cn } from "cn";

/**
 * The booking page (FR-11, FR-12), time first: choose a date, sport and length, then a start
 * time, then one of the courts free for the whole booking. The total comes from the API's quote,
 * and logged-in players with a verified email book it (FR-15): paid online on Stripe's page while
 * the court is held (FR-16, FR-22), or at the venue (ADR-021), as the venue allows.
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
  const today = todayInMalaysia();
  // Just after midnight, last night's late hours come first (ADR-015).
  const lastNight = lastNightDate(today, hourInMalaysia());
  const dates = [...(lastNight ? [lastNight] : []), ...bookingDates(today, booking.booking_window_days)];
  const lengths = lengthOptions(booking);
  const [date, setDate] = useState(today);
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
        options={dates.map((day) => {
          const chip = dateChip(day);
          const label = day === lastNight ? `${chip.day} night` : day === today ? "Today" : chip.day;
          return { value: day, label, detail: chip.date };
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
        <BookAction
          slug={slug}
          booking={
            start && court && quote.data ? { court_id: court.id, sport, starts_at: start.starts_at, minutes } : null
          }
          total={quote.data?.price}
          phone={phone}
          rules={booking}
        />
      </section>
    </div>
  );
}

/**
 * The Book buttons, or what the player must do first: log in (FR-36) or verify their email (FR-35).
 * Each choice of court, time and way of paying gets one Idempotency-Key, kept for every try of that
 * choice, so pressing twice or retrying after a network error never makes two bookings.
 */
function BookAction({
  slug,
  booking,
  total,
  phone,
  rules,
}: {
  slug: string;
  booking: Omit<BookingInput, "pay"> | null;
  total: string | undefined;
  phone: string | null;
  rules: PublicVenue["booking"];
}) {
  const me = useMe();
  const book = useBook(slug);
  const pay = usePay();
  const router = useRouter();
  const [press, setPress] = useState<{ choice: string; key: string }>();

  const onBook = (how: BookingInput["pay"]) => {
    if (!booking) return;
    const choice = `${booking.court_id}|${booking.starts_at}|${booking.minutes}|${booking.sport}|${how}`;
    const key = press?.choice === choice ? press.key : crypto.randomUUID();
    setPress({ choice, key });
    book.mutate(
      { ...booking, pay: how, key },
      {
        onSuccess: (made) =>
          how === "online"
            ? // Straight on to Stripe's page. If that fails, the status page offers to try again.
              pay.mutate(made.code, { onError: () => router.push(`/bookings/${made.code}/status`) })
            : router.push(`/bookings/${made.code}`),
      },
    );
  };

  const help = phone ? (
    <>
      Questions? Call the venue:{" "}
      <a href={`tel:${phone}`} className="font-medium text-foreground underline">
        {formatPhone(phone)}
      </a>
    </>
  ) : null;

  // The server can't know who is logged in, so it draws nothing here; the browser's first draw must match.
  const hydrated = useHydrated();
  if (!hydrated || me.isPending) return null;

  if (!me.data) {
    return (
      <div className="grid gap-2">
        <ButtonLink href={`/login?next=${encodeURIComponent(`/v/${slug}/book`)}`} className="h-11 justify-self-start px-6">
          Log in to book
        </ButtonLink>
        <p className="text-sm text-muted-foreground">
          You need a free CourtKu account to book. No account yet?{" "}
          <a href="/register" className="font-medium text-foreground underline">
            Sign up
          </a>
          .
        </p>
      </div>
    );
  }

  if (!me.data.email_verified) {
    return (
      <p className="text-sm">
        Verify your email before your first booking: open the link we sent to {me.data.email}.{" "}
        <a href="/verify-email" className="font-medium underline">
          Send it again
        </a>
      </p>
    );
  }

  const busy = book.isPending || pay.isPending || pay.isSuccess;
  return (
    <div className="grid gap-3">
      <div className="flex flex-wrap gap-2">
        {rules.pay_online && (
          <Button className="h-11 px-6" disabled={!booking || busy} onClick={() => onBook("online")}>
            {busy && press?.choice.endsWith("|online") ? "Going to payment..." : total ? `Pay ${total} online` : "Pay online"}
          </Button>
        )}
        {rules.pay_at_counter && (
          <Button
            variant={rules.pay_online ? "outline" : "default"}
            className="h-11 px-6"
            disabled={!booking || busy}
            onClick={() => onBook("counter")}
          >
            {book.isPending && press?.choice.endsWith("|counter") ? "Booking..." : "Book now, pay at the venue"}
          </Button>
        )}
      </div>
      {book.error && (
        <p className="text-sm text-destructive" role="alert">
          {book.error instanceof ApiError ? book.error.message : "Could not reach CourtKu. Check your connection and try again."}
        </p>
      )}
      {rules.pay_online && (
        <p className="text-sm text-muted-foreground">
          Paying online: we keep the court for you for {rules.hold_minutes} minutes while you pay on Stripe&apos;s secure
          page. <DemoNotice />
        </p>
      )}
      <p className="text-sm text-muted-foreground">
        {rules.pay_at_counter && "Paying at the venue: you pay at the counter when you arrive. "}
        Free cancellation online until {rules.cancel_cutoff_hours} hours before the start. {help}
      </p>
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
