"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { DaySchedule } from "@/components/venues/day-schedule";
import { VenuePage } from "@/components/venues/venue-page";

/** The day schedule for the counter (FR-31). Owners and staff. */
export default function VenueSchedulePage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Schedule</CardTitle>
            <CardDescription>
              Every court and booking of the day. Tap a booking to see who booked it. New online bookings appear within a
              minute.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <DaySchedule venueId={String(venue.id)} />
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}
