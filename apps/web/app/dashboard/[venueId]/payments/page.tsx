"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { PaymentSettingsForm, StripeAccountSection } from "@/components/venues/payments-section";
import { VenuePage } from "@/components/venues/venue-page";

/**
 * Payments (FR-17, FR-39, ADR-009): the venue's own Stripe account, so money goes straight to the
 * venue, and how players pay. Owners only.
 */
export default function VenuePaymentsPage() {
  return (
    <VenuePage>
      {(venue) =>
        venue.role === "owner" ? (
          <>
            <Card>
              <CardHeader>
                <CardTitle>Stripe account</CardTitle>
                <CardDescription>
                  Players pay online on Stripe&apos;s own page, and the money goes straight to your own Stripe account.
                  CourtKu never sees card or bank details, and never asks for your Stripe keys.
                </CardDescription>
              </CardHeader>
              <CardContent>
                <StripeAccountSection venueId={String(venue.id)} />
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle>How players pay</CardTitle>
              </CardHeader>
              <CardContent>
                {/* key: connecting or disconnecting changes the choices, so the form starts again from the saved values. */}
                <PaymentSettingsForm key={String(venue.payment_connected)} venue={venue} />
              </CardContent>
            </Card>
          </>
        ) : (
          <Card>
            <CardHeader>
              <CardTitle>Payments</CardTitle>
              <CardDescription>Only the venue owner can see and change payments.</CardDescription>
            </CardHeader>
          </Card>
        )
      }
    </VenuePage>
  );
}
