import { describe, expect, it } from "vitest";
import { priceRuleSchema, toPriceRulePayload } from "./price-rule";

const valid = {
  name: "Weekday peak",
  court_id: "",
  weekdays: ["1", "2", "3", "4", "5"],
  starts_at: "18:00",
  ends_at: "00:00",
  price: "30.00",
};

describe("priceRuleSchema", () => {
  it("needs a day, a price, and different start and end times", () => {
    const result = priceRuleSchema.safeParse({ ...valid, weekdays: [], price: "", ends_at: "18:00" });

    // All three at once, so the owner can fix everything in one go.
    expect(result.error?.issues.map((i) => [i.path.join("."), i.message])).toEqual([
      ["weekdays", "Choose at least one day."],
      ["price", "Enter the price per hour."],
      ["ends_at", "The end time must be different from the start time."],
    ]);
  });

  it("ADR-017 explains a price that isn't in 10-sen steps", () => {
    expect(priceRuleSchema.safeParse({ ...valid, price: "30.05" }).error?.issues[0]?.message).toBe(
      "Use a price in 10-sen steps, e.g. 25.00 or 25.50.",
    );
  });
});

describe("toPriceRulePayload", () => {
  it("sends numbers, null for all courts, and no empty name", () => {
    expect(toPriceRulePayload({ ...valid, name: " ", weekdays: ["5", "1"], price: "30.5" })).toEqual({
      name: null,
      court_id: null,
      weekdays: [1, 5],
      starts_at: "18:00",
      ends_at: "00:00",
      price_sen: 3050,
    });
    expect(toPriceRulePayload({ ...valid, court_id: "12" }).court_id).toBe(12);
  });
});
