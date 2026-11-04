"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { FormField, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { StockPhoto } from "@/components/venues/stock-photo";
import { type VenueCard, type VenueFilters, useVenueList } from "@/features/venues/public-list";
import { SPORTS, sportLabel } from "@/lib/sports";
import { MALAYSIAN_STATES, stateLabel } from "@/lib/states";

/**
 * The venues guests can book, on the home page (ADR-031): filter by sport and state, search by name
 * or city, and open a venue's page. Real venues come first, then the made-up demo venues (ADR-030),
 * which say so on their own page.
 */
export function VenueList() {
  const [sport, setSport] = useState("");
  const [state, setState] = useState("");
  const [typed, setTyped] = useState("");
  // Search after a short pause in typing, not on every key.
  const [q, setQ] = useState("");
  useEffect(() => {
    const timer = setTimeout(() => setQ(typed), 300);
    return () => clearTimeout(timer);
  }, [typed]);

  const filters: VenueFilters = { sport, state, q };
  const list = useVenueList(filters);
  const venues = list.data?.pages.flatMap((page) => page.data) ?? [];
  const total = list.data?.pages[0]?.meta.total;

  return (
    <section aria-labelledby="venues-heading" className="grid gap-4">
      <div className="grid gap-1">
        <h2 id="venues-heading" className="text-2xl font-semibold tracking-tight">
          Find a court
        </h2>
        <p className="text-sm text-muted-foreground">
          {total === undefined ? "Venues you can book on CourtKu." : `${total} ${total === 1 ? "venue" : "venues"} you can book.`}
        </p>
      </div>

      <div className="grid gap-3 sm:grid-cols-3">
        <SelectField
          label="Sport"
          name="sport"
          options={SPORTS}
          placeholder="Any sport"
          value={sport}
          onChange={(e) => setSport(e.target.value)}
        />
        <SelectField
          label="State"
          name="state"
          options={MALAYSIAN_STATES}
          placeholder="Any state"
          value={state}
          onChange={(e) => setState(e.target.value)}
        />
        <FormField
          label="Search"
          name="q"
          type="search"
          placeholder="Venue name or city"
          maxLength={60}
          value={typed}
          onChange={(e) => setTyped(e.target.value)}
          className="h-11"
        />
      </div>

      {list.isPending && <p className="text-sm text-muted-foreground">Loading venues...</p>}
      {list.isError && <p className="text-sm text-destructive">Could not load the venues. Please refresh the page.</p>}
      {list.isSuccess && venues.length === 0 && (
        <p className="rounded-lg border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
          No venues match. Try another sport, state or search.
        </p>
      )}

      <ul className={`grid gap-4 sm:grid-cols-2 lg:grid-cols-3 ${list.isPlaceholderData ? "opacity-60" : ""}`}>
        {venues.map((venue) => (
          <li key={venue.slug}>
            <VenueCardLink venue={venue} sport={sport} />
          </li>
        ))}
      </ul>

      {list.hasNextPage && (
        <Button variant="outline" className="h-11 justify-self-center px-6" disabled={list.isFetchingNextPage} onClick={() => list.fetchNextPage()}>
          {list.isFetchingNextPage ? "Loading..." : "Show more venues"}
        </Button>
      )}
    </section>
  );
}

function VenueCardLink({ venue, sport }: { venue: VenueCard; sport: string }) {
  const where = [venue.city, stateLabel(venue.state)].filter(Boolean).join(", ");

  return (
    <Link
      href={`/v/${venue.slug}`}
      className="group grid h-full overflow-hidden rounded-xl ring-1 ring-foreground/10 transition-shadow hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
    >
      {venue.cover_url ? (
        <Image
          src={venue.cover_url}
          alt=""
          width={600}
          height={400}
          unoptimized // Cloudinary already sends the right size and format.
          className="aspect-[3/2] w-full object-cover"
        />
      ) : (
        // No photo yet: a sample photo of its main sport, so every card has a picture.
        <StockPhoto sport={venue.main_sport} seed={venue.slug} width={600} height={400} className="aspect-[3/2] w-full" />
      )}
      <div className="grid gap-2 p-4">
        <h3 className="font-semibold leading-snug group-hover:underline">{venue.name}</h3>
        <p className="text-sm text-muted-foreground">{where}</p>
        <ul className="flex flex-wrap gap-1.5" aria-label="Sports">
          {venue.sports.map((s) => (
            <li key={s} className="rounded-full bg-secondary px-2 py-0.5 text-xs text-secondary-foreground">
              {sportLabel(s)}
            </li>
          ))}
        </ul>
        <p className="text-sm">
          <span className="text-muted-foreground">From </span>
          <span className="font-medium">{venue.from_price}</span>
          <span className="text-muted-foreground"> an hour{sport ? ` for ${sportLabel(sport).toLowerCase()}` : ""}</span>
        </p>
      </div>
    </Link>
  );
}
