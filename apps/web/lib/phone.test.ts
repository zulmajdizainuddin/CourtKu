import { describe, expect, it } from "vitest";
import { formatPhone, normalizePhone } from "./phone";

describe("formatPhone", () => {
  it("shows stored numbers the way Malaysians write them", () => {
    expect(formatPhone("+60123456789")).toBe("012-345 6789");
    expect(formatPhone("+601123456789")).toBe("011-2345 6789");
    expect(formatPhone("+60312345678")).toBe("03-1234 5678");
    expect(formatPhone("+6041234567")).toBe("04-123 4567");
  });
});

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
