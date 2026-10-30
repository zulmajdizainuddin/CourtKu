"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { PriceRulesSection } from "@/components/venues/price-rules-section";
import { VenuePage } from "@/components/venues/venue-page";

/** Normal, peak and off-peak prices (FR-09, FR-10). Owners change them; staff can only read them. */
export default function VenuePricesPage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Prices</CardTitle>
            <CardDescription>
              {venue.role === "owner"
                ? "Charge more at busy times and less at quiet ones. Players pay for each half hour at the price of that half hour."
                : "Only the venue owner can change the prices."}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <PriceRulesSection venueId={String(venue.id)} canEdit={venue.role === "owner"} />
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}
