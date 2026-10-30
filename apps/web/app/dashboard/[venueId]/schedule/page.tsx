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
              Every court and booking of the day. Tap a booking or block to see the details. Add walk-in and phone
              bookings, or block a time for repairs or a private event. New online bookings appear within a minute.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <DaySchedule
              venueId={String(venue.id)}
              rules={{ slot_minutes: venue.slot_minutes, min_minutes: venue.min_minutes, max_minutes: venue.max_minutes }}
            />
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}
