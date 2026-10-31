"use client";

import Link from "next/link";
import { ApiError } from "@/lib/api";
import { formatDay } from "@/lib/format";
import { formatTime } from "@/lib/opening-hours";

type ConflictingBooking = { code: string; court: string; starts_at: string; business_date: string; customer: string | null };

/**
 * FR-38: when the API refuses a settings change because upcoming bookings would be stranded
 * (BOOKINGS_CONFLICT), list those bookings, each linking to its day on the Schedule, where the
 * owner can cancel it. Shows nothing for any other error.
 */
export function BookingConflictList({ error, venueId }: { error: unknown; venueId: string }) {
  if (!(error instanceof ApiError) || error.code !== "BOOKINGS_CONFLICT") return null;
  const bookings = (error.details.bookings ?? []) as ConflictingBooking[];
  if (bookings.length === 0) return null;

  return (
    <div className="grid gap-2 rounded-lg bg-destructive/10 px-3 py-2 text-sm">
      <p className="font-medium text-destructive">Bookings in the way</p>
      <ul className="grid gap-1">
        {bookings.map((b) => (
          <li key={b.code}>
            <Link href={`/dashboard/${venueId}/schedule?date=${b.business_date}`} className="underline">
              {b.code}
            </Link>{" "}
            · {b.court} · {formatDay(b.starts_at.slice(0, 10))}, {formatTime(b.starts_at.slice(11, 16))}
            {b.customer && ` · ${b.customer}`}
          </li>
        ))}
      </ul>
    </div>
  );
}
