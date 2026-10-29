"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { StaffSection } from "@/components/venues/staff-section";
import { VenuePage } from "@/components/venues/venue-page";

/** Staff and invites (FR-05). Owners only: the list shows people's email addresses. */
export default function VenueStaffPage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Staff</CardTitle>
            <CardDescription>
              {venue.role === "owner"
                ? "People who help run the venue. Staff will handle the day's schedule and walk-ins; they can't change courts, hours or prices."
                : "Only the venue owner can see and manage the staff."}
            </CardDescription>
          </CardHeader>
          {venue.role === "owner" && (
            <CardContent>
              <StaffSection venueId={String(venue.id)} />
            </CardContent>
          )}
        </Card>
      )}
    </VenuePage>
  );
}
