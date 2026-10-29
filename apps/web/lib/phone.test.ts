import { describe, expect, it } from "vitest";
import { normalizePhone } from "./phone";

describe("normalizePhone", () => {
  it("turns any way of typing a Malaysian number into +60 format", () => {
    expect(normalizePhone("012-345 6789")).toBe("+60123456789");
    expect(normalizePhone("011-2345 6789")).toBe("+601123456789");
    expect(normalizePhone("(03) 1234 5678")).toBe("+60312345678");
    expect(normalizePhone("+60 12-345 6789")).toBe("+60123456789");
  });

  it("refuses numbers that aren't Malaysian", () => {
    expect(normalizePhone("+65 9123 4567")).toBeNull();
    expect(normalizePhone("012-345")).toBeNull();
    expect(normalizePhone("123456789")).toBeNull();
    expect(normalizePhone("012-CALL-NOW")).toBeNull();
  });
});
