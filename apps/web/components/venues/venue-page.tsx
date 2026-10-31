"use client";

import Link from "next/link";
import { useParams, usePathname } from "next/navigation";
import { Suspense } from "react";
import { TwoFactorGate } from "@/components/auth/two-factor-gate";
import { VerifyEmailFirst } from "@/components/auth/verify-email-first";
import { VenueStatusBadge } from "@/components/venues/venue-status-badge";
import { RequireUser } from "@/features/auth/require-user";
import { type ManageVenue, useManageVenue } from "@/features/venues/hooks";
import { ApiError } from "@/lib/api";
import { cn } from "cn";

/**
 * The frame around every page of one venue in the dashboard (/dashboard/[venueId]/...):
 * login, verified email and two-factor checks, the venue's name and status, and the menu.
 * The page itself only says what goes inside: <VenuePage>{(venue) => ...}</VenuePage>
 */
export function VenuePage({ children }: { children: (venue: ManageVenue) => React.ReactNode }) {
  // The venue id comes from the URL, which is only known when someone opens the page.
  return (
    <Suspense>
      <VenuePageRoute>{children}</VenuePageRoute>
    </Suspense>
  );
}

function VenuePageRoute({ children }: { children: (venue: ManageVenue) => React.ReactNode }) {
  const { venueId } = useParams<{ venueId: string }>();
  return (
    <RequireUser>
      {(user) =>
        user.email_verified ? (
          <TwoFactorGate user={user}>
            <VenueFrame venueId={venueId}>{children}</VenueFrame>
          </TwoFactorGate>
        ) : (
          <VerifyEmailFirst />
        )
      }
    </RequireUser>
  );
}

function VenueFrame({ venueId, children }: { venueId: string; children: (venue: ManageVenue) => React.ReactNode }) {
  const { data: venue, isPending, error } = useManageVenue(venueId);

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-10">
      <Link href="/dashboard" className="text-sm text-muted-foreground hover:underline">
        ← Your venues
      </Link>

      {isPending && <p className="text-sm text-muted-foreground">Loading...</p>}

      {error && (
        <p className="text-sm text-destructive">
          {error instanceof ApiError && error.status === 404
            ? "This venue doesn't exist, or you don't work there."
            : "Could not load this venue. Please refresh the page."}
        </p>
      )}

      {/* Never show a venue the API just refused, even if the browser still remembers it. */}
      {venue && !error && (
        <>
          <div className="flex flex-wrap items-center gap-2">
            <h1 className="text-2xl font-semibold tracking-tight">{venue.name}</h1>
            <VenueStatusBadge status={venue.status} />
          </div>
          <VenueMenu venueId={venueId} isOwner={venue.role === "owner"} />
          {children(venue)}
        </>
      )}
    </div>
  );
}

function VenueMenu({ venueId, isOwner }: { venueId: string; isOwner: boolean }) {
  const pathname = usePathname();
  const base = `/dashboard/${venueId}`;
  const items = [
    { href: base, label: "Overview" },
    { href: `${base}/schedule`, label: "Schedule" },
    { href: `${base}/profile`, label: "Profile" },
    { href: `${base}/hours`, label: "Hours" },
    { href: `${base}/courts`, label: "Courts" },
    { href: `${base}/prices`, label: "Prices" },
    { href: `${base}/photos`, label: "Photos" },
    { href: `${base}/settings`, label: "Settings" },
    // Only owners manage staff and money; the API refuses everyone else anyway.
    ...(isOwner
      ? [
          { href: `${base}/payments`, label: "Payments" },
          { href: `${base}/staff`, label: "Staff" },
        ]
      : []),
  ];

  return (
    // The grey line under the menu is an inset shadow, so the current tab's underline can sit
    // on top of it without sticking out (which would make the menu scroll up and down).
    <nav aria-label="Venue" className="-mb-2 flex gap-1 overflow-x-auto shadow-[inset_0_-1px_0_var(--border)]">
      {items.map((item) => {
        const current = pathname === item.href;
        return (
          <Link
            key={item.href}
            href={item.href}
            aria-current={current ? "page" : undefined}
            className={cn(
              "border-b-2 px-2.5 py-2 text-sm whitespace-nowrap",
              current
                ? "border-foreground font-medium"
                : "border-transparent text-muted-foreground hover:text-foreground",
            )}
          >
            {item.label}
          </Link>
        );
      })}
    </nav>
  );
}
