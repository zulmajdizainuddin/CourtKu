"use client";

import { useState } from "react";
import { TwoFactorGate } from "@/components/auth/two-factor-gate";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { VenueStatusBadge } from "@/components/venues/venue-status-badge";
import { type AdminVenue, useAdminVenues, useChangeVenueStatus } from "@/features/admin/hooks";
import { RequireUser } from "@/features/auth/require-user";
import type { VenueStatus } from "@/features/venues/hooks";
import { ApiError } from "@/lib/api";
import { formatDate } from "@/lib/format";
import { stateLabel } from "@/lib/states";

/** The platform admin's page: approve new venues, suspend bad ones (FR-04, FR-33). */
export default function AdminPage() {
  return (
    <RequireUser>
      {(user) =>
        user.is_platform_admin ? (
          <TwoFactorGate user={user}>
            <AdminVenues />
          </TwoFactorGate>
        ) : (
          // The API refuses every admin request anyway; this only avoids showing an empty page.
          <p className="mx-auto w-full max-w-3xl px-4 py-10 text-sm text-muted-foreground">
            This page is for the CourtKu team only.
          </p>
        )
      }
    </RequireUser>
  );
}

const filters: { value: VenueStatus | "all"; label: string }[] = [
  { value: "pending", label: "Waiting" },
  { value: "active", label: "Live" },
  { value: "suspended", label: "Suspended" },
  { value: "all", label: "All" },
];

function AdminVenues() {
  const [status, setStatus] = useState<VenueStatus | "all">("pending");
  const venues = useAdminVenues(status);
  const changeStatus = useChangeVenueStatus();
  const list = venues.data?.pages.flatMap((page) => page.data) ?? [];

  const change = (venue: AdminVenue, action: "approve" | "suspend") => {
    if (action === "suspend" && !window.confirm(`Hide ${venue.name} from players?`)) return;
    changeStatus.mutate({ venueId: venue.id, action });
  };

  return (
    <div className="mx-auto grid w-full max-w-3xl gap-6 px-4 py-10">
      <h1 className="text-2xl font-semibold tracking-tight">Venues</h1>

      <div className="flex flex-wrap gap-2" role="group" aria-label="Show venues">
        {filters.map((filter) => (
          <Button
            key={filter.value}
            variant={status === filter.value ? "default" : "outline"}
            aria-pressed={status === filter.value}
            onClick={() => setStatus(filter.value)}
          >
            {filter.label}
          </Button>
        ))}
      </div>

      <FormAlert
        message={
          changeStatus.error instanceof ApiError
            ? changeStatus.error.message
            : changeStatus.error
              ? "Could not reach CourtKu. Please try again."
              : undefined
        }
      />

      {venues.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {venues.isError && <p className="text-sm text-destructive">Could not load venues. Please refresh the page.</p>}
      {venues.isSuccess && list.length === 0 && <p className="text-sm text-muted-foreground">No venues here.</p>}

      <ul className="grid gap-3">
        {list.map((venue) => (
          <li key={venue.id} className="grid gap-3 rounded-xl px-4 py-3 ring-1 ring-foreground/10">
            <div className="flex flex-wrap items-start justify-between gap-2">
              <div className="min-w-0">
                <p className="font-medium">{venue.name}</p>
                <p className="text-sm text-muted-foreground">
                  /v/{venue.slug} · {stateLabel(venue.state)} · listed {formatDate(venue.created_at)}
                </p>
              </div>
              <VenueStatusBadge status={venue.status} />
            </div>

            <p className="text-sm break-words">
              <span className="text-muted-foreground">Owner: </span>
              {venue.owners.map((owner) => `${owner.name} (${owner.email})`).join(", ") || "none"}
            </p>

            <div className="flex gap-2">
              {venue.status !== "active" && (
                <Button disabled={changeStatus.isPending} onClick={() => change(venue, "approve")}>
                  {venue.status === "suspended" ? "Reinstate" : "Approve"}
                </Button>
              )}
              {venue.status !== "suspended" && (
                <Button variant="destructive" disabled={changeStatus.isPending} onClick={() => change(venue, "suspend")}>
                  {venue.status === "pending" ? "Turn down" : "Suspend"}
                </Button>
              )}
            </div>
          </li>
        ))}
      </ul>

      {venues.hasNextPage && (
        <Button
          variant="outline"
          className="justify-self-center"
          disabled={venues.isFetchingNextPage}
          onClick={() => venues.fetchNextPage()}
        >
          {venues.isFetchingNextPage ? "Loading..." : "Show more"}
        </Button>
      )}
    </div>
  );
}
