import { describe, expect, it } from "vitest";
import { durationOptions, formatDuration } from "./duration";

describe("formatDuration", () => {
  it("writes minutes the way people say them", () => {
    expect(formatDuration(30)).toBe("30 minutes");
    expect(formatDuration(60)).toBe("1 hour");
    expect(formatDuration(90)).toBe("1 hour 30 minutes");
    expect(formatDuration(180)).toBe("3 hours");
  });
});

describe("durationOptions", () => {
  it("offers only whole numbers of slots", () => {
    expect(durationOptions(60, 240)).toEqual([60, 120, 180, 240]);
    expect(durationOptions(30, 120)).toEqual([30, 60, 90, 120]);
  });
});
