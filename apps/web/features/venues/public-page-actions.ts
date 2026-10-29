"use server";

import { updateTag } from "next/cache";
import { publicVenueTag } from "@/lib/public-venue";
import { SLUG_PATTERN } from "@/lib/slug";

/**
 * A server action: runs on the Next.js server, called from the dashboard after a save.
 * Throws away the cached copy of the venue's public page, so the next visit shows the change.
 *
 * Anyone could call it, but it can only make the page fetch fresh data from the API,
 * which shows only public, approved information anyway.
 */
export async function refreshPublicVenuePage(slug: string): Promise<void> {
  if (SLUG_PATTERN.test(slug)) updateTag(publicVenueTag(slug));
}
