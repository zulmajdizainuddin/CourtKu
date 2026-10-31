"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { Suspense } from "react";
import { DemoNotice } from "@/components/booking/demo-notice";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { RequireUser } from "@/features/auth/require-user";
import { useBooking, usePay } from "@/features/booking/hooks";
import { ApiError } from "@/lib/api";
import type { PlayerBooking } from "@/lib/booking";
import { endLabel } from "@/lib/booking";
import { formatDay, formatDayTime } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";

/**
 * Where Stripe sends the player back after paying (plan section 9, step 9). It shows CourtKu's own
 * booking status, never anything from the address Stripe used (ADR-006). While the booking waits for
 * payment it asks every few seconds; the API asks Stripe, so "paid" shows as soon as Stripe knows.
 */
export default function PaymentStatusPage() {
  return (
    <Suspense>
      <RequireUser>{() => <PaymentStatus />}</RequireUser>
    </Suspense>
  );
}

function PaymentStatus() {
  const { code } = useParams<{ code: string }>();
  const { data: booking, isPending, error } = useBooking(code, { watchPayment: true });

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
      {booking && !error && (
        <>
          <header className="grid gap-1">
            <p className="text-sm text-muted-foreground">
              {booking.code} · {booking.venue.name}
            </p>
            <h1 className="text-2xl font-semibold tracking-tight">{headline(booking)}</h1>
            <p className="text-sm">
              {booking.court.name} · {formatDay(booking.starts_at.slice(0, 10))},{" "}
              {formatTime(booking.starts_at.slice(11, 16))} –{" "}
              {endLabel(booking.starts_at.slice(11, 16), booking.minutes)} · {booking.total}
            </p>
          </header>
          <Details booking={booking} />
        </>
      )}
    </div>
  );
}

/** The one line that says how things stand, from CourtKu's own records. */
function headline(booking: PlayerBooking): string {
  const payment = booking.payment;
  if (payment?.problem) return "Your payment needs the venue's help";
  switch (booking.status) {
    case "pending_payment":
      return payment?.status === "failed" || payment?.status === "expired"
        ? "Your payment didn't go through"
        : "Waiting for your payment";
    case "expired":
      return "The time to pay ran out";
    case "cancelled":
      return "This booking was cancelled";
    default:
      return booking.paid ? "Paid. You're booked!" : "You're booked";
  }
}

function Details({ booking }: { booking: PlayerBooking }) {
  const pay = usePay();
  const payment = booking.payment;

  // The money came in but couldn't confirm the booking: the venue refunds it (FR-25).
  if (payment?.problem) {
    const why = {
      amount_mismatch: "The amount paid didn't match the booking's total.",
      booking_expired: "Your payment arrived after the time to pay had run out.",
      booking_cancelled: "The booking was cancelled before your payment arrived.",
      already_paid: "This booking was already paid.",
    }[payment.problem];
    return (
      <div className="grid gap-3 text-sm">
        <p>{why} The venue can see this and will refund you. Questions? Contact the venue with your booking code.</p>
        <ButtonLink href={`/bookings/${booking.code}`} variant="outline" className="h-11 justify-self-start px-6">
          View booking
        </ButtonLink>
      </div>
    );
  }

  if (booking.status === "pending_payment") {
    const failed = payment?.status === "failed" || payment?.status === "expired";
    return (
      <div className="grid gap-4 text-sm">
        {failed ? (
          <p>Nothing was charged. You can try again with another card while we keep the court for you.</p>
        ) : (
          <p className="flex items-center gap-2" aria-live="polite">
            <span className="size-2 animate-pulse rounded-full bg-amber-500" aria-hidden />
            Checking with Stripe... This page updates by itself as soon as your payment is confirmed.
          </p>
        )}
        {booking.hold_expires_at && (
          <p>
            We keep the court for you until <strong>{formatDayTime(booking.hold_expires_at)}</strong>. If it isn&apos;t
            paid by then, it goes back to other players.
          </p>
        )}
        <div className="flex flex-wrap gap-2">
          <Button
            className="h-11 px-6"
            disabled={pay.isPending || pay.isSuccess}
            onClick={() => pay.mutate(booking.code)}
          >
            {pay.isPending || pay.isSuccess
              ? "Opening Stripe..."
              : failed
                ? "Try paying again"
                : "Back to the payment page"}
          </Button>
          <ButtonLink href={`/bookings/${booking.code}`} variant="ghost" className="h-11 px-6">
            Booking details
          </ButtonLink>
        </div>
        {pay.error && (
          <p className="text-destructive" role="alert">
            {pay.error instanceof ApiError
              ? pay.error.message
              : "Could not reach CourtKu. Check your connection and try again."}
          </p>
        )}
        <p className="text-muted-foreground">
          <DemoNotice />
        </p>
      </div>
    );
  }

  if (booking.status === "expired") {
    return (
      <div className="grid gap-3 text-sm">
        <p>
          Nothing was charged, and the court went back to other players. You can book again if it&apos;s still free.
        </p>
        <ButtonLink href={`/v/${booking.venue.slug}/book`} className="h-11 justify-self-start px-6">
          Book again
        </ButtonLink>
      </div>
    );
  }

  return (
    <div className="grid gap-3 text-sm">
      {booking.paid && booking.payment_method === "online" && (
        <p>Paid online. Show your booking code {booking.code} at the counter when you arrive.</p>
      )}
      <div className="flex flex-wrap gap-2">
        <ButtonLink href={`/bookings/${booking.code}`} className="h-11 px-6">
          View booking
        </ButtonLink>
        <Link href="/me/bookings" className="inline-flex h-11 items-center px-2 underline">
          My bookings
        </Link>
      </div>
    </div>
  );
}
