"use client";

import Link from "next/link";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { RequireUser } from "@/features/auth/require-user";
import { useMyBookings } from "@/features/booking/hooks";
import { endLabel, type PlayerBooking } from "@/lib/booking";
import { formatDay } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";
import { sportLabel } from "@/lib/sports";
import { cn } from "cn";

/** The player's bookings (FR-30): upcoming ones first, past and cancelled ones on the other tab. */
export default function MyBookingsPage() {
  return <RequireUser>{() => <MyBookings />}</RequireUser>;
}

const TABS = [
  { value: "upcoming", label: "Upcoming" },
  { value: "past", label: "Past and cancelled" },
] as const;

function MyBookings() {
  const [when, setWhen] = useState<"upcoming" | "past">("upcoming");
  const bookings = useMyBookings(when);
  // Never show bookings the API just refused, even if the browser still remembers them.
  const list = bookings.isError ? [] : (bookings.data?.pages.flatMap((page) => page.data) ?? []);

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-10">
      <h1 className="text-2xl font-semibold tracking-tight">My bookings</h1>

      <div role="group" aria-label="Which bookings" className="flex gap-2">
        {TABS.map((tab) => (
          <Button
            key={tab.value}
            variant={when === tab.value ? "default" : "outline"}
            aria-pressed={when === tab.value}
            className="h-11 px-4"
            onClick={() => setWhen(tab.value)}
          >
            {tab.label}
          </Button>
        ))}
      </div>

      {bookings.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {bookings.isError && <p className="text-sm text-destructive">Could not load your bookings. Please refresh the page.</p>}
      {bookings.isSuccess && list.length === 0 && (
        <p className="text-sm text-muted-foreground">
          {when === "upcoming" ? "No upcoming bookings." : "No past bookings yet."}
        </p>
      )}

      {list.length > 0 && (
        <ul className="grid gap-2">
          {list.map((booking) => (
            <li key={booking.code}>
              <BookingCard booking={booking} />
            </li>
          ))}
        </ul>
      )}

      {bookings.hasNextPage && (
        <Button
          variant="outline"
          className="h-11 justify-self-start px-6"
          disabled={bookings.isFetchingNextPage}
          onClick={() => bookings.fetchNextPage()}
        >
          {bookings.isFetchingNextPage ? "Loading..." : "Show more"}
        </Button>
      )}

      {when === "upcoming" && bookings.isSuccess && list.length === 0 && (
        <ButtonLink href="/" variant="outline" className="h-11 justify-self-start px-6">
          Find a court
        </ButtonLink>
      )}
    </div>
  );
}

const STATUS_LABELS: Record<PlayerBooking["status"], string> = {
  confirmed: "Booked",
  checked_in: "Checked in",
  completed: "Played",
  no_show: "No-show",
  cancelled: "Cancelled",
};

function BookingCard({ booking }: { booking: PlayerBooking }) {
  const start = booking.starts_at.slice(11, 16);
  return (
    <Link
      href={`/bookings/${booking.code}`}
      className="flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg px-3 py-2 ring-1 ring-foreground/10 hover:bg-muted"
    >
      <div className="min-w-40 flex-1">
        <p className="font-medium">{booking.venue.name}</p>
        <p className="text-sm">
          {formatDay(booking.starts_at.slice(0, 10))}, {formatTime(start)} – {endLabel(start, booking.minutes)}
        </p>
        <p className="text-sm text-muted-foreground">
          {booking.court.name}
          {booking.sport && ` · ${sportLabel(booking.sport)}`} · {booking.total} · {booking.code}
        </p>
      </div>
      <span
        className={cn(
          "rounded-full px-2 py-0.5 text-xs",
          booking.status === "cancelled" ? "bg-destructive/10 text-destructive" : "bg-muted text-muted-foreground",
        )}
      >
        {STATUS_LABELS[booking.status]}
      </span>
    </Link>
  );
}
