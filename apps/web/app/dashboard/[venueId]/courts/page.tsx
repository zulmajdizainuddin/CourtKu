"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { CourtsSection } from "@/components/venues/courts-section";
import { VenuePage } from "@/components/venues/venue-page";

/** The venue's courts (FR-08). Owners change them; staff can only read them. */
export default function VenueCourtsPage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Courts</CardTitle>
            <CardDescription>
              {venue.role === "owner"
                ? "Each court players can book. A court can offer more than one sport, e.g. futsal and badminton in one hall."
                : "Only the venue owner can change the courts."}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <CourtsSection venueId={String(venue.id)} canEdit={venue.role === "owner"} />
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}
