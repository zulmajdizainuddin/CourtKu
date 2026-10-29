"use client";

import Link from "next/link";
import { VerifyEmailFirst } from "@/components/auth/verify-email-first";
import { Button } from "@/components/ui/button";
import { Card, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenueStatusBadge } from "@/components/venues/venue-status-badge";
import { RequireUser } from "@/features/auth/require-user";
import { useMyVenues } from "@/features/venues/hooks";
import { stateLabel } from "@/lib/states";

/** The venue picker for owners and staff: every venue this user works at. */
export default function DashboardPage() {
  return <RequireUser>{(user) => (user.email_verified ? <VenuePicker /> : <VerifyEmailFirst />)}</RequireUser>;
}

function VenuePicker() {
  const { data: venues, isPending, isError } = useMyVenues();

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-10">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold tracking-tight">Your venues</h1>
        <Button render={<Link href="/venues/new" />} nativeButton={false}>
          List a venue
        </Button>
      </div>

      {isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {isError && <p className="text-sm text-destructive">Could not load your venues. Please refresh the page.</p>}

      {venues?.length === 0 && (
        <Card>
          <CardHeader>
            <CardTitle>No venues yet</CardTitle>
            <CardDescription>
              Own a badminton, futsal or pickleball venue? List it on CourtKu and take bookings online.
              If you work at a venue, ask the owner to invite you.
            </CardDescription>
          </CardHeader>
        </Card>
      )}

      {venues && venues.length > 0 && (
        <ul className="grid gap-3">
          {venues.map((venue) => (
            <li key={venue.id}>
              <Link
                href={`/dashboard/${venue.id}`}
                className="flex items-center justify-between gap-3 rounded-xl px-4 py-3 ring-1 ring-foreground/10 transition-colors hover:bg-muted"
              >
                <div className="min-w-0">
                  <p className="truncate font-medium">{venue.name}</p>
                  <p className="text-sm text-muted-foreground">
                    {stateLabel(venue.state)} · {venue.role === "owner" ? "Owner" : "Staff"}
                  </p>
                </div>
                <VenueStatusBadge status={venue.status} />
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
