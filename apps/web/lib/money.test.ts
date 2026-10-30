import { describe, expect, it } from "vitest";
import { formatRinggit, parseRinggit, senToInput } from "./money";

describe("parseRinggit", () => {
  it("turns what an owner types into whole sen", () => {
    expect(parseRinggit("25")).toBe(2500);
    expect(parseRinggit("25.5")).toBe(2550);
    expect(parseRinggit("25.05")).toBe(2505);
    expect(parseRinggit(" RM 1,250.50 ")).toBe(125050);
    expect(parseRinggit("0.10")).toBe(10);
  });

  it("refuses anything that isn't an amount of ringgit", () => {
    expect(parseRinggit("")).toBeNull();
    expect(parseRinggit("abc")).toBeNull();
    expect(parseRinggit("25.555")).toBeNull();
    expect(parseRinggit("-5")).toBeNull();
    expect(parseRinggit("25.")).toBeNull();
  });
});

describe("formatRinggit and senToInput", () => {
  it("show sen as ringgit without decimal maths", () => {
    expect(formatRinggit(2550)).toBe("RM 25.50");
    expect(formatRinggit(125005)).toBe("RM 1,250.05");
    expect(senToInput(2500)).toBe("25.00");
    expect(senToInput(5)).toBe("0.05");
  });
});
