"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { PhotosSection } from "@/components/venues/photos-section";
import { VenuePage } from "@/components/venues/venue-page";

/** The venue's photos (FR-06). Owners change them; staff can only see them. */
export default function VenuePhotosPage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Photos</CardTitle>
            <CardDescription>
              {venue.role === "owner"
                ? "Players see these on your venue's page. Photos are made smaller before upload, so large phone photos are fine."
                : "Only the venue owner can change the photos."}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <PhotosSection venueId={String(venue.id)} canEdit={venue.role === "owner"} />
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}
