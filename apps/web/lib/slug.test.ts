import { describe, expect, it } from "vitest";
import { SLUG_PATTERN, slugify } from "./slug";

describe("slugify", () => {
  it("turns a venue name into a web address", () => {
    expect(slugify("Arena Sg. Buloh!")).toBe("arena-sg-buloh");
    expect(slugify("  Café   Badminton 2  ")).toBe("cafe-badminton-2");
  });

  it("never ends with a hyphen, even when cut to 60 characters", () => {
    const slug = slugify(`${"a".repeat(59)} b`);
    expect(slug).toBe("a".repeat(59));
    expect(slug).toMatch(SLUG_PATTERN);
  });

  it("returns an empty string when nothing usable is left", () => {
    expect(slugify("!!!")).toBe("");
  });
});

describe("SLUG_PATTERN", () => {
  it("accepts what the API accepts and refuses the rest", () => {
    expect("arena-sg-buloh").toMatch(SLUG_PATTERN);
    expect("Arena").not.toMatch(SLUG_PATTERN);
    expect("arena--sg").not.toMatch(SLUG_PATTERN);
    expect("-arena").not.toMatch(SLUG_PATTERN);
  });
});
