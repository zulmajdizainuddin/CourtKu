import Link from "next/link";
import { Suspense } from "react";
import { VerifiedNotice } from "@/components/auth/verified-notice";

// Landing page. Kept simple; the real one (for venues and players) comes later.
export default function Home() {
  return (
    <div className="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 px-4 py-16">
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
      {/* Honest about what works today: booking is online, payment is at the counter until M4 (ADR-021). */}
      <p className="max-w-xl rounded-lg border border-dashed px-4 py-3 text-sm text-muted-foreground">
        <span className="font-medium text-foreground">Booking is open.</span> Book online and pay at the
        counter when you arrive. Paying online is coming soon.
      </p>
      <p className="max-w-xl text-sm text-muted-foreground">
        Run a venue?{" "}
        <Link href="/venues/new" className="font-medium text-foreground underline">
          Add your venue to CourtKu
        </Link>{" "}
        and take bookings online.
      </p>
    </div>
  );
}
