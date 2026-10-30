"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { Suspense } from "react";
import { ButtonLink } from "@/components/ui/button-link";
import { RequireUser } from "@/features/auth/require-user";
import { useBooking, useCancelBooking } from "@/features/booking/hooks";
import { Button } from "@/components/ui/button";
import type { PlayerBooking } from "@/lib/booking";
import { ApiError } from "@/lib/api";
import { endLabel } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { formatDay, formatDayTime } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";
import { formatPhone } from "@/lib/phone";
import { sportLabel } from "@/lib/sports";

/**
 * One of the player's bookings (FR-18, FR-24): the code to show at the counter, what they booked,
 * and cancelling it until the venue's cutoff.
 */
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

      {/* Never show a booking the API just refused, even if the browser still remembers it. */}
      {booking && !error && (
        <>
          <header className="grid gap-1">
            <p
              className={
                booking.status === "cancelled"
                  ? "text-sm font-medium text-destructive"
                  : "text-sm font-medium text-emerald-700 dark:text-emerald-400"
              }
            >
              {STATUS_HEADINGS[booking.status]}
            </p>
            <h1 className="text-3xl font-semibold tracking-tight">{booking.venue.name}</h1>
          </header>

          <section aria-label="Booking code" className="grid gap-1 rounded-xl p-4 text-center ring-1 ring-foreground/10">
            <p className="text-sm text-muted-foreground">
              {booking.status === "confirmed" ? "Show this code at the counter" : "Booking code"}
            </p>
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
            {booking.status === "confirmed" && (
              <>
                <dt className="text-muted-foreground">Payment</dt>
                <dd>Pay at the counter when you arrive</dd>
              </>
            )}
          </dl>

          <CancelSection booking={booking} />

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

const STATUS_HEADINGS: Record<PlayerBooking["status"], string> = {
  confirmed: "You're booked",
  checked_in: "Checked in",
  completed: "Played",
  no_show: "Marked as a no-show",
  cancelled: "Cancelled",
};

/** Cancel until the venue's cutoff (FR-24); after it, the player calls the venue. */
function CancelSection({ booking }: { booking: PlayerBooking }) {
  const cancel = useCancelBooking(booking.code);

  if (booking.status === "cancelled") {
    return (
      <p className="text-sm text-muted-foreground">
        Cancelled{booking.cancelled_at && ` on ${formatDayTime(booking.cancelled_at)}`}. The time is free for others again.
      </p>
    );
  }
  if (booking.status !== "confirmed") return null;

  const onCancel = () => {
    if (window.confirm(`Cancel ${booking.code} at ${booking.venue.name}? The time goes back to other players.`)) {
      cancel.mutate();
    }
  };

  return booking.can_cancel ? (
    <div className="grid gap-2">
      <p className="text-sm text-muted-foreground">Free cancellation online until {formatDayTime(booking.cancel_until)}.</p>
      <Button variant="outline" className="h-11 justify-self-start px-6 text-destructive" disabled={cancel.isPending} onClick={onCancel}>
        {cancel.isPending ? "Cancelling..." : "Cancel booking"}
      </Button>
      {cancel.error && (
        <p className="text-sm text-destructive" role="alert">
          {cancel.error instanceof ApiError ? cancel.error.message : "Could not reach CourtKu. Check your connection and try again."}
        </p>
      )}
    </div>
  ) : (
    <p className="text-sm text-muted-foreground">
      Online cancelling closed on {formatDayTime(booking.cancel_until)}. To cancel now, call the venue.
    </p>
  );
}
