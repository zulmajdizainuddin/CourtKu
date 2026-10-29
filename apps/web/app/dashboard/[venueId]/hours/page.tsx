"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ClosuresSection } from "@/components/venues/closures-section";
import { OpeningHoursForm, OpeningHoursView } from "@/components/venues/opening-hours-form";
import { VenuePage } from "@/components/venues/venue-page";
import type { ManageVenue } from "@/features/venues/hooks";
import { useOpeningHours } from "@/features/venues/hours";

/** Opening hours and closed dates (FR-07). Owners change them; staff can only read them. */
export default function VenueHoursPage() {
  return <VenuePage>{(venue) => <Hours venue={venue} />}</VenuePage>;
}

function Hours({ venue }: { venue: ManageVenue }) {
  const venueId = String(venue.id);
  const hours = useOpeningHours(venueId);
  const isOwner = venue.role === "owner";

  return (
    <div className="grid gap-6">
      <Card>
        <CardHeader>
          <CardTitle>Opening hours</CardTitle>
          <CardDescription>
            {isOwner
              ? "When players can book. Closing after midnight is fine, e.g. 8:00 am to 2:00 am."
              : "Only the venue owner can change the opening hours."}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {hours.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
          {hours.isError && <p className="text-sm text-destructive">Could not load the hours. Please refresh the page.</p>}
          {hours.data &&
            (isOwner ? <OpeningHoursForm venueId={venueId} saved={hours.data} /> : <OpeningHoursView saved={hours.data} />)}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Closed dates</CardTitle>
          <CardDescription>Public holidays, renovation or private events when the whole venue is closed.</CardDescription>
        </CardHeader>
        <CardContent>
          <ClosuresSection venueId={venueId} canEdit={isOwner} />
        </CardContent>
      </Card>
    </div>
  );
}
