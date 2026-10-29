import type { ManageVenue } from "@/features/venues/hooks";

/** One line of the owner's set-up checklist on the Overview tab. */
export type SetupItem = {
  label: string;
  done: boolean;
  /** Needed before players can use the venue; the rest is optional but helps players. */
  needed: boolean;
  /** Why it matters, in a few words. */
  why: string;
  /** The dashboard tab where it is set up: /dashboard/[venueId]/{tab} */
  tab: "profile" | "hours" | "courts";
};

/**
 * Everything an owner sets up, needed items first, each with a tick when done.
 * Hours and courts come from their own API calls, so only their counts are passed in.
 */
export function setupChecklist(venue: ManageVenue, hoursCount: number, courtsCount: number): SetupItem[] {
  return [
    { label: "Address", done: venue.address_line !== null, needed: true, why: "Players use it to find you", tab: "profile" },
    { label: "Opening hours", done: hoursCount > 0, needed: true, why: "Until then, the venue shows as closed", tab: "hours" },
    { label: "Courts", done: courtsCount > 0, needed: true, why: "Players book a court", tab: "courts" },
    { label: "Phone number", done: venue.phone !== null, needed: false, why: "So players can call you", tab: "profile" },
    { label: "Facilities", done: venue.facilities.length > 0, needed: false, why: "Parking, surau, showers...", tab: "profile" },
  ];
}
