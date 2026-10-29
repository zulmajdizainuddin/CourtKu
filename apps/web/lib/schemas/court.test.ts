import { describe, expect, it } from "vitest";
import { courtSchema, toCourtPayload } from "./court";

describe("courtSchema", () => {
  it("needs a name and at least one sport", () => {
    const result = courtSchema.safeParse({ name: " ", sports: [], location: "indoor", is_active: true });

    expect(result.error?.issues.map((i) => i.message)).toEqual([
      "Give the court a name, e.g. Court 1.",
      "Choose at least one sport.",
    ]);
  });
});

describe("toCourtPayload", () => {
  it("sends indoor or outdoor as true or false", () => {
    expect(
      toCourtPayload({ name: " Court 1 ", sports: ["futsal", "badminton"], location: "outdoor", is_active: true }),
    ).toEqual({ name: "Court 1", sports: ["futsal", "badminton"], is_indoor: false, is_active: true });
  });
});
