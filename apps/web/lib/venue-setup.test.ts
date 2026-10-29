import { describe, expect, it } from "vitest";
import type { ManageVenue } from "@/features/venues/hooks";
import { setupChecklist } from "./venue-setup";

const venue = (overrides: Partial<ManageVenue>): ManageVenue => ({
  id: 1,
  name: "Arena Sg. Buloh",
  slug: "arena-sg-buloh",
  status: "active",
  state: "selangor",
  role: "owner",
  description: null,
  address_line: null,
  city: null,
  postcode: null,
  phone: null,
  facilities: [],
  approved_at: null,
  created_at: "2026-10-29T00:00:00+00:00",
  ...overrides,
});

const summary = (items: ReturnType<typeof setupChecklist>) =>
  items.map((i) => `${i.done ? "✓" : "✗"} ${i.label}${i.needed ? "" : " (optional)"}`);

describe("setupChecklist", () => {
  it("shows a new venue everything to set up, needed items first", () => {
    expect(summary(setupChecklist(venue({}), { hours: 0, courts: 0, photos: 0 }))).toEqual([
      "✗ Address",
      "✗ Opening hours",
      "✗ Courts",
      "✗ Phone number (optional)",
      "✗ Facilities (optional)",
      "✗ Photos (optional)",
    ]);
  });

  it("ticks each item on its own, e.g. facilities without an address", () => {
    expect(summary(setupChecklist(venue({ facilities: ["parking"] }), { hours: 7, courts: 0, photos: 2 }))).toEqual([
      "✗ Address",
      "✓ Opening hours",
      "✗ Courts",
      "✗ Phone number (optional)",
      "✓ Facilities (optional)",
      "✓ Photos (optional)",
    ]);
  });
});
