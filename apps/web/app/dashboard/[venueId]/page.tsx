"use client";

import Link from "next/link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenuePage } from "@/components/venues/venue-page";
import type { ManageVenue } from "@/features/venues/hooks";
import { stateLabel } from "@/lib/states";

/** One venue's home in the dashboard: what its status means and what to do next. */
export default function VenueOverviewPage() {
  return <VenuePage>{(venue) => <Overview venue={venue} />}</VenuePage>;
}

const statusHelp: Record<ManageVenue["status"], (venue: ManageVenue) => string> = {
  pending: () => "CourtKu checks every new venue before players can see it. You can set everything up while you wait.",
  active: (venue) => `Your venue is approved. Its public page is /v/${venue.slug}.`,
  suspended: () => "Your venue is hidden from players. Contact the CourtKu team to find out why.",
};

function Overview({ venue }: { venue: ManageVenue }) {
  const profileStarted = venue.address_line !== null || venue.phone !== null;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Status</CardTitle>
        <CardDescription>
          {stateLabel(venue.state)} · You are the {venue.role === "owner" ? "owner" : "staff"}
        </CardDescription>
      </CardHeader>
      <CardContent className="grid gap-3 text-sm">
        <p>{statusHelp[venue.status](venue)}</p>
        {venue.role === "owner" && !profileStarted && (
          <p>
            Next step:{" "}
            <Link href={`/dashboard/${venue.id}/profile`} className="font-medium underline">
              add your address, phone number and facilities
            </Link>
            . Opening hours and courts come after that.
          </p>
        )}
      </CardContent>
    </Card>
  );
}
