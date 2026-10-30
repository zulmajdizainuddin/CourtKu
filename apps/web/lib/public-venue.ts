import { cacheLife, cacheTag } from "next/cache";
import type { OpeningPeriod } from "@/lib/opening-hours";
import { SLUG_PATTERN } from "@/lib/slug";
import { stateLabel } from "@/lib/states";

// A venue's public page /v/[slug] (FR-06): what guests see without logging in.

/** The API's PublicVenueResource: only what players need, never the owner or staff. */
export type PublicVenue = {
  name: string;
  slug: string;
  description: string | null;
  address_line: string | null;
  city: string | null;
  postcode: string | null;
  state: string;
  phone: string | null;
  facilities: string[];
  photos: { id: number; url: string; thumbnail_url: string; width: number; height: number }[];
  opening_hours: (OpeningPeriod & { closes_next_day: boolean })[];
  closures: { id: number; starts_on: string; ends_on: string; reason: string }[];
  courts: { id: number; name: string; is_indoor: boolean; base_price: string | null; sports: string[] }[];
  /** How players may book (FR-13): lengths in minutes and how many days ahead. */
  booking: {
    slot_minutes: number;
    min_minutes: number;
    max_minutes: number;
    booking_window_days: number;
    /** Players cancel online until this many hours before the start (FR-24). */
    cancel_cutoff_hours: number;
  };
};

// Only the Next.js server uses this address; the browser never talks to the API directly here.
const apiUrl = process.env.API_URL ?? "http://127.0.0.1:8000";

/** The label on a venue's cached public page, so a dashboard save can throw that copy away. */
export function publicVenueTag(slug: string): string {
  return `public-venue:${slug}`;
}

/**
 * Fetches a venue for its public page, or null when there is no live venue at that address.
 * Kept for a minute ("use cache"), so a busy page asks the API at most about once a minute.
 * When the owner saves something in the dashboard, refreshPublicVenuePage() throws the copy
 * away at once, so the owner sees their change straight away.
 */
export async function getPublicVenue(slug: string): Promise<PublicVenue | null> {
  "use cache";
  cacheLife("minutes");
  cacheTag(publicVenueTag(slug));

  // Not a possible web address: don't even ask the API.
  if (!SLUG_PATTERN.test(slug)) return null;

  const response = await fetch(`${apiUrl}/api/v1/venues/${slug}`, { headers: { Accept: "application/json" } });
  if (response.status === 404) return null;
  if (!response.ok) throw new Error(`The venue page could not be loaded (API answered ${response.status}).`);

  return ((await response.json()) as { data: PublicVenue }).data;
}

/** "Lot 12, Jalan Industri 3, 47000 Sungai Buloh, Selangor" (skipping parts the owner left empty) */
export function fullAddress(venue: Pick<PublicVenue, "address_line" | "postcode" | "city" | "state">): string {
  const town = [venue.postcode, venue.city].filter(Boolean).join(" ");
  return [venue.address_line, town, stateLabel(venue.state)].filter(Boolean).join(", ");
}

/** Opens Google Maps searching for the venue: its name and address. */
export function directionsUrl(venue: Pick<PublicVenue, "name" | "address_line" | "postcode" | "city" | "state">): string {
  const query = `${venue.name}, ${fullAddress(venue)}, Malaysia`;
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
}
