import { describe, expect, it } from "vitest";
import { courtSchema, toCourtPayload } from "./court";

const valid = { name: "Court 1", price: "25.00", sports: ["badminton"], location: "indoor" as const, is_active: true };

describe("courtSchema", () => {
  it("needs a name and at least one sport", () => {
    const result = courtSchema.safeParse({ ...valid, name: " ", sports: [] });

    expect(result.error?.issues.map((i) => i.message)).toEqual([
      "Give the court a name, e.g. Court 1.",
      "Choose at least one sport.",
    ]);
  });

  it("ADR-017 explains a price that isn't in 10-sen steps or isn't a price", () => {
    const message = (price: string) => courtSchema.safeParse({ ...valid, price }).error?.issues[0]?.message;

    expect(message("25.25")).toBe("Use a price in 10-sen steps, e.g. 25.00 or 25.50.");
    expect(message("twenty")).toBe("Enter a price like 25.00.");
    expect(message("1000.10")).toBe("The price can be at most RM 1,000.00 an hour.");
    expect(courtSchema.safeParse({ ...valid, price: "" }).success).toBe(true);
  });
});

describe("toCourtPayload", () => {
  it("sends the price as whole sen, and indoor or outdoor as true or false", () => {
    expect(toCourtPayload({ ...valid, name: " Court 1 ", price: "25.5", location: "outdoor" })).toEqual({
      name: "Court 1",
      base_price_sen: 2550,
      sports: ["badminton"],
      is_indoor: false,
      is_active: true,
    });
  });

  it("sends no price as null, so the court stays hidden from players", () => {
    expect(toCourtPayload({ ...valid, price: " " }).base_price_sen).toBeNull();
  });
});
