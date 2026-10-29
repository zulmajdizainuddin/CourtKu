"use client";

import { Check, Circle } from "lucide-react";
import Link from "next/link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenuePage } from "@/components/venues/venue-page";
import { useCourts } from "@/features/venues/courts";
import type { ManageVenue } from "@/features/venues/hooks";
import { useOpeningHours } from "@/features/venues/hours";
import { usePhotos } from "@/features/venues/photos";
import { stateLabel } from "@/lib/states";
import { setupChecklist } from "@/lib/venue-setup";

/** One venue's home in the dashboard: what its status means and what is left to set up. */
export default function VenueOverviewPage() {
  return (
    <VenuePage>
      {(venue) => (
        <div className="grid gap-6">
          <Status venue={venue} />
          {venue.role === "owner" && <SetupChecklist venue={venue} />}
        </div>
      )}
    </VenuePage>
  );
}

const statusHelp: Record<ManageVenue["status"], (venue: ManageVenue) => string> = {
  pending: () => "CourtKu checks every new venue before players can see it. You can set everything up while you wait.",
  active: (venue) => `Your venue is approved. Its public page is /v/${venue.slug}.`,
  suspended: () => "Your venue is hidden from players. Contact the CourtKu team to find out why.",
};

function Status({ venue }: { venue: ManageVenue }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Status</CardTitle>
        <CardDescription>
          {stateLabel(venue.state)} · You are the {venue.role === "owner" ? "owner" : "staff"}
        </CardDescription>
      </CardHeader>
      <CardContent className="text-sm">
        <p>{statusHelp[venue.status](venue)}</p>
      </CardContent>
    </Card>
  );
}

/** Everything an owner sets up, with a tick on what is done; each line opens the right tab. */
function SetupChecklist({ venue }: { venue: ManageVenue }) {
  const venueId = String(venue.id);
  const hours = useOpeningHours(venueId);
  const courts = useCourts(venueId);
  const photos = usePhotos(venueId);

  if (!hours.isSuccess || !courts.isSuccess || !photos.isSuccess) return null;

  const items = setupChecklist(venue, {
    hours: hours.data.length,
    courts: courts.data.length,
    photos: photos.data.length,
  });
  const doneCount = items.filter((item) => item.done).length;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Set up your venue</CardTitle>
        <CardDescription>
          {doneCount === items.length ? "Everything is set up." : `${doneCount} of ${items.length} done.`}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <ul className="grid gap-1">
          {items.map((item) => (
            <li key={item.label}>
              <Link
                href={`/dashboard/${venue.id}/${item.tab}`}
                className="flex min-h-11 items-center gap-3 rounded-lg px-2 text-sm hover:bg-muted"
              >
                {item.done ? (
                  <Check className="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" aria-hidden="true" />
                ) : (
                  <Circle className="size-5 shrink-0 text-muted-foreground" aria-hidden="true" />
                )}
                <span className="sr-only">{item.done ? "Done:" : "To do:"}</span>
                <span className="min-w-0 flex-1">
                  <span className={item.done ? "text-muted-foreground" : "font-medium"}>{item.label}</span>
                  <span className="text-muted-foreground">
                    {" · "}
                    {item.needed ? item.why : `optional · ${item.why}`}
                  </span>
                </span>
              </Link>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  );
}
