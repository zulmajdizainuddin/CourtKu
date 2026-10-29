"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { Suspense } from "react";
import { TwoFactorGate } from "@/components/auth/two-factor-gate";
import { VerifyEmailFirst } from "@/components/auth/verify-email-first";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenueStatusBadge } from "@/components/venues/venue-status-badge";
import { RequireUser } from "@/features/auth/require-user";
import { type ManageVenue, useManageVenue } from "@/features/venues/hooks";
import { ApiError } from "@/lib/api";
import { stateLabel } from "@/lib/states";

/** One venue's home in the dashboard. The profile, hours and courts pages hang off this later. */
export default function VenueHomePage() {
  // The venue id comes from the URL, which is only known when someone opens the page.
  return (
    <Suspense>
      <VenueHomeRoute />
    </Suspense>
  );
}

function VenueHomeRoute() {
  const { venueId } = useParams<{ venueId: string }>();
  return (
    <RequireUser>
      {(user) =>
        user.email_verified ? (
          <TwoFactorGate user={user}>
            <VenueHome venueId={venueId} />
          </TwoFactorGate>
        ) : (
          <VerifyEmailFirst />
        )
      }
    </RequireUser>
  );
}

const statusHelp: Record<ManageVenue["status"], (venue: ManageVenue) => string> = {
  pending: () =>
    "CourtKu checks every new venue before players can see it. Next you'll add your address, opening hours, courts and photos; those pages will appear here.",
  active: (venue) => `Your venue is approved. Its public page is /v/${venue.slug}.`,
  suspended: () => "Your venue is hidden from players. Contact the CourtKu team to find out why.",
};

function VenueHome({ venueId }: { venueId: string }) {
  const { data: venue, isPending, error } = useManageVenue(venueId);

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-10">
      <Link href="/dashboard" className="text-sm text-muted-foreground hover:underline">
        ← Your venues
      </Link>

      {isPending && <p className="text-sm text-muted-foreground">Loading...</p>}

      {error && (
        <p className="text-sm text-destructive">
          {error instanceof ApiError && error.status === 404
            ? "This venue doesn't exist, or you don't work there."
            : "Could not load this venue. Please refresh the page."}
        </p>
      )}

      {venue && (
        <Card>
          <CardHeader>
            <div className="flex flex-wrap items-center gap-2">
              <CardTitle className="text-xl">{venue.name}</CardTitle>
              <VenueStatusBadge status={venue.status} />
            </div>
            <CardDescription>
              {stateLabel(venue.state)} · You are the {venue.role === "owner" ? "owner" : "staff"}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm">{statusHelp[venue.status](venue)}</p>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
