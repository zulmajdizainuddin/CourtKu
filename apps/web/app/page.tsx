import Link from "next/link";
import { Suspense } from "react";
import { VerifiedNotice } from "@/components/auth/verified-notice";
import { VenueList } from "@/components/venues/venue-list";

// Landing page: what CourtKu does, and the venues guests can book straight away (ADR-031).
export default function Home() {
  return (
    <div className="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-10 px-4 py-16">
      <div className="flex flex-col gap-6">
        <Suspense>
          <VerifiedNotice />
        </Suspense>
        <h1 className="max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">
          Book a court in under two minutes.
        </h1>
        <p className="max-w-xl text-lg text-muted-foreground">
          See live availability for badminton, futsal and pickleball courts, book online, and get your
          booking code instantly.
        </p>
        <p className="max-w-xl text-sm text-muted-foreground">
          Run a venue?{" "}
          <Link href="/venues/new" className="font-medium text-foreground underline">
            Add your venue to CourtKu
          </Link>{" "}
          and take bookings online.
        </p>
      </div>

      <VenueList />
    </div>
  );
}
