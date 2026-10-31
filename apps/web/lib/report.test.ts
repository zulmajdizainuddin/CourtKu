import { describe, expect, it } from "vitest";
import { addDays, chartBars, daysBetween, niceMax, presetRange, rangeProblem } from "./report";

describe("presetRange", () => {
  it("ends the last 7 and 30 days today", () => {
    expect(presetRange("last-7", "2026-11-02")).toEqual({ from: "2026-10-27", to: "2026-11-02" });
    expect(presetRange("last-30", "2026-11-02")).toEqual({ from: "2026-10-04", to: "2026-11-02" });
  });

  it("covers whole calendar months, across the new year and in a leap year", () => {
    expect(presetRange("this-month", "2026-11-02")).toEqual({ from: "2026-11-01", to: "2026-11-30" });
    expect(presetRange("last-month", "2027-01-15")).toEqual({ from: "2026-12-01", to: "2026-12-31" });
    expect(presetRange("last-month", "2028-03-01")).toEqual({ from: "2028-02-01", to: "2028-02-29" });
  });
});

describe("date maths", () => {
  it("adds days across month ends and counts both ends of a range", () => {
    expect(addDays("2026-10-30", 3)).toBe("2026-11-02");
    expect(daysBetween("2026-11-01", "2026-11-30")).toBe(30);
    expect(daysBetween("2026-11-02", "2026-11-02")).toBe(1);
  });
});

describe("rangeProblem", () => {
  it("accepts up to a year, in order", () => {
    expect(rangeProblem({ from: "2026-01-01", to: "2026-12-31" })).toBeUndefined();
    expect(rangeProblem({ from: "2026-11-04", to: "2026-11-02" })).toBe("The end date can't be before the start date.");
    expect(rangeProblem({ from: "2026-01-01", to: "2027-01-02" })).toBe("Choose at most a year at once.");
    expect(rangeProblem({ from: "", to: "2026-11-02" })).toBe("Choose a start and an end date.");
  });
});

describe("chartBars", () => {
  it("gives one bar per day for a month", () => {
    const bars = chartBars([
      { date: "2026-11-02", bookings: 2, booked_sen: 6000 },
      { date: "2026-11-03", bookings: 0, booked_sen: 0 },
    ]);
    expect(bars.map((b) => [b.label, b.longLabel, b.bookings])).toEqual([
      ["2 Nov", "Mon, 2 Nov 2026", 2],
      ["3 Nov", "Tue, 3 Nov 2026", 0],
    ]);
  });

  it("adds days up into months for long ranges", () => {
    const days = Array.from({ length: 90 }, (_, i) => ({ date: addDays("2026-10-01", i), bookings: 1, booked_sen: 2000 }));
    expect(chartBars(days).map((b) => [b.label, b.longLabel, b.bookings, b.booked_sen])).toEqual([
      ["Oct", "October 2026", 31, 62000],
      ["Nov", "November 2026", 30, 60000],
      ["Dec", "December 2026", 29, 58000],
    ]);
  });
});

describe("niceMax", () => {
  it("rounds the top of the scale up to a clean number", () => {
    expect([0, 1, 7, 23, 60, 101, 4500].map(niceMax)).toEqual([1, 1, 10, 25, 100, 200, 5000]);
  });
});
