import { describe, expect, it, vi } from "vitest";

// "use cache" and cacheLife only work inside Next.js; these tests only need the address helpers.
vi.mock("next/cache", () => ({ cacheLife: () => {} }));

const { directionsUrl, fullAddress } = await import("./public-venue");

const venue = {
  name: "Arena Sg Buloh",
  address_line: "Lot 12, Jalan Industri 3",
  postcode: "47000",
  city: "Sungai Buloh",
  state: "selangor",
};

describe("fullAddress", () => {
  it("joins the address parts, skipping the empty ones", () => {
    expect(fullAddress(venue)).toBe("Lot 12, Jalan Industri 3, 47000 Sungai Buloh, Selangor");
    expect(fullAddress({ ...venue, address_line: null, postcode: null })).toBe("Sungai Buloh, Selangor");
  });
});

describe("directionsUrl", () => {
  it("searches Google Maps for the venue's name and address", () => {
    const url = new URL(directionsUrl(venue));

    expect(url.origin + url.pathname).toBe("https://www.google.com/maps/search/");
    expect(url.searchParams.get("query")).toBe("Arena Sg Buloh, Lot 12, Jalan Industri 3, 47000 Sungai Buloh, Selangor, Malaysia");
  });
});
