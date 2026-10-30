"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { Suspense } from "react";
import { ButtonLink } from "@/components/ui/button-link";
import { RequireUser } from "@/features/auth/require-user";
import { useBooking } from "@/features/booking/hooks";
import { ApiError } from "@/lib/api";
import { endLabel } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { formatDay } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";
import { formatPhone } from "@/lib/phone";
import { sportLabel } from "@/lib/sports";

/** The player's booking confirmation (FR-18): the code to show at the counter, and what they booked. */
export default function BookingConfirmationPage() {
  // The code comes from the URL, which is only known when someone opens the page.
  return (
    <Suspense>
      <RequireUser>{() => <Confirmation />}</RequireUser>
    </Suspense>
  );
}

function Confirmation() {
  const { code } = useParams<{ code: string }>();
  const { data: booking, isPending, error } = useBooking(code);

  return (
    <div className="mx-auto grid w-full max-w-xl gap-6 px-4 py-10">
      {isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {error && (
        <p className="text-sm text-destructive">
          {error instanceof ApiError && error.status === 404
            ? "We couldn't find this booking in your account."
            : "Could not load the booking. Please refresh the page."}
        </p>
      )}

      {booking && (
        <>
          <header className="grid gap-1">
            <p className="text-sm font-medium text-emerald-700 dark:text-emerald-400">
              {booking.status === "cancelled" ? "Cancelled" : "You're booked"}
            </p>
            <h1 className="text-3xl font-semibold tracking-tight">{booking.venue.name}</h1>
          </header>

          <section aria-label="Booking code" className="grid gap-1 rounded-xl p-4 text-center ring-1 ring-foreground/10">
            <p className="text-sm text-muted-foreground">Show this code at the counter</p>
            <p className="font-mono text-3xl font-semibold tracking-widest">{booking.code}</p>
          </section>

          <dl className="grid grid-cols-[7rem_1fr] gap-x-3 gap-y-2 text-sm">
            <dt className="text-muted-foreground">Date</dt>
            <dd>{formatDay(booking.starts_at.slice(0, 10))}</dd>
            <dt className="text-muted-foreground">Time</dt>
            <dd>
              {formatTime(booking.starts_at.slice(11, 16))} – {endLabel(booking.starts_at.slice(11, 16), booking.minutes)} (
              {formatDuration(booking.minutes)})
            </dd>
            <dt className="text-muted-foreground">Court</dt>
            <dd>
              {booking.court.name}
              {booking.sport && ` · ${sportLabel(booking.sport)}`}
            </dd>
            <dt className="text-muted-foreground">Total</dt>
            <dd className="font-semibold">{booking.total}</dd>
            <dt className="text-muted-foreground">Payment</dt>
            <dd>Pay at the counter when you arrive</dd>
          </dl>

          {booking.venue.phone && (
            <p className="text-sm text-muted-foreground">
              Need to change something? Call the venue:{" "}
              <a href={`tel:${booking.venue.phone}`} className="font-medium text-foreground underline">
                {formatPhone(booking.venue.phone)}
              </a>
            </p>
          )}

          <div className="flex flex-wrap gap-2">
            <ButtonLink href={`/v/${booking.venue.slug}/book`} className="h-11 px-6">
              Book another court
            </ButtonLink>
            <Link href={`/v/${booking.venue.slug}`} className="inline-flex h-11 items-center px-2 text-sm underline">
              Venue details
            </Link>
          </div>
        </>
      )}
    </div>
  );
}
