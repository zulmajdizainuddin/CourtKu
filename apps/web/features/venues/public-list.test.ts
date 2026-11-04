import { describe, expect, it } from "vitest";
import { venueListPath } from "./public-list";

describe("venueListPath", () => {
  it("asks for every venue when nothing is chosen", () => {
    expect(venueListPath({ sport: "", state: "", q: "  " }, 1)).toBe("/api/v1/venues");
  });

  it("sends only the filters that are set, the search trimmed, and the page after the first", () => {
    expect(venueListPath({ sport: "futsal", state: "", q: " cheras " }, 2)).toBe("/api/v1/venues?sport=futsal&q=cheras&page=2");
    expect(venueListPath({ sport: "", state: "pulau-pinang", q: "" }, 1)).toBe("/api/v1/venues?state=pulau-pinang");
  });

  it("encodes what people type", () => {
    expect(venueListPath({ sport: "", state: "", q: "Kota Bharu & co" }, 1)).toBe("/api/v1/venues?q=Kota+Bharu+%26+co");
  });
});
