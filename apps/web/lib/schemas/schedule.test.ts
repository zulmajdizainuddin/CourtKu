import { describe, expect, it } from "vitest";
import { blockSchema, counterBookingSchema, toBlockPayload, toCounterBookingPayload } from "./schedule";

const walkIn = {
  source: "walk_in" as const,
  court_id: "6",
  sport: "badminton",
  starts_at: "2026-11-06T18:00:00+08:00",
  minutes: "60",
  customer_name: " Ali bin Abu ",
  customer_phone: "012-345 6789",
};

describe("counterBookingSchema", () => {
  it("FR-20 needs the customer's name and a Malaysian phone number", () => {
    const result = counterBookingSchema.safeParse({ ...walkIn, customer_name: " ", customer_phone: "12345" });

    expect(result.error?.issues.map((i) => i.message)).toEqual([
      "Enter the customer's name.",
      "Enter a Malaysian phone number, e.g. 012-345 6789.",
    ]);
  });

  it("sends numbers, a trimmed name and the phone in one format", () => {
    expect(toCounterBookingPayload(counterBookingSchema.parse(walkIn))).toEqual({
      source: "walk_in",
      court_id: 6,
      sport: "badminton",
      starts_at: "2026-11-06T18:00:00+08:00",
      minutes: 60,
      customer_name: "Ali bin Abu",
      customer_phone: "+60123456789",
    });
  });
});

describe("blockSchema", () => {
  it("FR-21 needs at least one court and a reason", () => {
    const result = blockSchema.safeParse({ court_ids: [], starts_at: "2026-11-06T18:00:00+08:00", minutes: "120", reason: "" });

    expect(result.error?.issues.map((i) => i.message)).toEqual([
      "Choose at least one court.",
      "Say why, e.g. floor repair or private event.",
    ]);
  });

  it("sends the court ids and length as numbers", () => {
    expect(
      toBlockPayload({ court_ids: ["6", "7"], starts_at: "2026-11-06T18:00:00+08:00", minutes: "120", reason: " Floor repair " }),
    ).toEqual({ court_ids: [6, 7], starts_at: "2026-11-06T18:00:00+08:00", minutes: 120, reason: "Floor repair" });
  });
});
