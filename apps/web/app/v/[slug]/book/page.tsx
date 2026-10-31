import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Suspense } from "react";
import { BookingPanel } from "@/components/booking/booking-panel";
import { venueSports } from "@/lib/booking";
import { DemoVenueNotice } from "@/components/venues/demo-venue-notice";
import { getPublicVenue } from "@/lib/public-venue";

// A venue's booking page (FR-11, FR-12): free times and prices, without logging in.
// The venue's details come from the same cached copy as its public page; the free times
// are always asked fresh in the browser.

export async function generateMetadata({ params }: PageProps<"/v/[slug]/book">): Promise<Metadata> {
  const venue = await getPublicVenue((await params).slug);
  if (!venue) return { title: "Venue not found" };

  return {
    title: `Book a court at ${venue.name}`,
    description: `Free times and prices at ${venue.name} on CourtKu.`,
  };
}

export default function VenueBookingPage({ params }: PageProps<"/v/[slug]/book">) {
  return (
    <Suspense fallback={<p className="mx-auto w-full max-w-3xl px-4 py-10 text-sm text-muted-foreground">Loading...</p>}>
      <BookingContent params={params} />
    </Suspense>
  );
}

async function BookingContent({ params }: { params: PageProps<"/v/[slug]/book">["params"] }) {
  const venue = await getPublicVenue((await params).slug);
  if (!venue) notFound();

  const sports = venueSports(venue.courts);

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-8">
      <Link href={`/v/${venue.slug}`} className="text-sm text-muted-foreground hover:underline">
        ← {venue.name}
      </Link>
      <header className="grid gap-1">
        <h1 className="text-3xl font-semibold tracking-tight">Book a court</h1>
        <p className="text-muted-foreground">{venue.name}</p>
      </header>
      {venue.is_demo && <DemoVenueNotice />}

      {sports.length === 0 ? (
        <p className="text-sm text-muted-foreground">This venue has no courts to book yet.</p>
      ) : (
        <BookingPanel slug={venue.slug} sports={sports} booking={venue.booking} phone={venue.phone} />
      )}
    </div>
  );
}
