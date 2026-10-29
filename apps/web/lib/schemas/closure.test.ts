import { describe, expect, it } from "vitest";
import { closureSchema, toClosurePayload } from "./closure";

describe("closureSchema", () => {
  it("accepts a range, or a single day with no last day", () => {
    expect(closureSchema.safeParse({ starts_on: "2027-03-30", ends_on: "2027-04-01", reason: "Hari Raya" }).success).toBe(true);
    expect(closureSchema.safeParse({ starts_on: "2027-03-30", ends_on: "", reason: "Hari Raya" }).success).toBe(true);
  });

  it("explains a last day before the first, or a missing reason", () => {
    const result = closureSchema.safeParse({ starts_on: "2027-04-01", ends_on: "2027-03-30", reason: " " });
    expect(result.error?.issues.map((i) => i.message)).toEqual([
      "Tell players why, e.g. Hari Raya or renovation.",
      "The last day can't be before the first day.",
    ]);
  });
});

describe("toClosurePayload", () => {
  it("turns an empty last day into a one-day closure", () => {
    expect(toClosurePayload({ starts_on: "2027-03-30", ends_on: "", reason: " Hari Raya " })).toEqual({
      starts_on: "2027-03-30",
      ends_on: "2027-03-30",
      reason: "Hari Raya",
    });
  });
});
