import { Suspense } from "react";
import { VerifiedNotice } from "@/components/auth/verified-notice";

// Landing page. M0 keeps it simple; the real one (for venues and players) comes later.
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
        See live availability for badminton, futsal and pickleball courts, pay online, and get your
        booking code instantly.
      </p>
      {/* Honest about what works today. Remove once players can book (M3). */}
      <p className="max-w-xl rounded-lg border border-dashed px-4 py-3 text-sm text-muted-foreground">
        <span className="font-medium text-foreground">Booking is coming soon.</span> Venues are being
        set up. You can already create an account so you&apos;re ready when the first courts go
        live.
      </p>
    </div>
  );
}
