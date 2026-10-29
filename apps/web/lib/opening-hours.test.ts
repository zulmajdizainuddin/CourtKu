import { describe, expect, it } from "vitest";
import {
  closesNextDay,
  formatPeriod,
  formatTime,
  HALF_HOURS,
  openingHoursProblems,
  periodsFromWeek,
  weekFromPeriods,
} from "./opening-hours";

const FRIDAY = 5;
const SATURDAY = 6;
const SUNDAY = 0;

describe("times", () => {
  it("offers every half hour of the day", () => {
    expect(HALF_HOURS).toHaveLength(48);
    expect(HALF_HOURS[0]).toBe("00:00");
    expect(HALF_HOURS[17]).toBe("08:30");
    expect(HALF_HOURS[47]).toBe("23:30");
  });

  it("shows times the Malaysian way, with am and pm", () => {
    expect(formatTime("08:00")).toBe("8:00 am");
    expect(formatTime("14:30")).toBe("2:30 pm");
    expect(formatTime("00:00")).toBe("12:00 am");
    expect(formatTime("12:00")).toBe("12:00 pm");
  });

  it("FR-37 marks periods that close after midnight", () => {
    expect(closesNextDay("08:00", "02:00")).toBe(true);
    expect(closesNextDay("08:00", "23:00")).toBe(false);
    expect(formatPeriod({ opens_at: "08:00", closes_at: "02:00" })).toBe("8:00 am – 2:00 am (next day)");
  });

  it("says midnight and open 24 hours in plain words", () => {
    expect(formatPeriod({ opens_at: "08:00", closes_at: "00:00" })).toBe("8:00 am – midnight");
    expect(formatPeriod({ opens_at: "00:00", closes_at: "00:00" })).toBe("Open 24 hours");
  });
});

describe("weekFromPeriods and periodsFromWeek", () => {
  it("turns the API's list into a week for the form and back", () => {
    const saved = [
      { weekday: FRIDAY, opens_at: "14:30", closes_at: "02:00" },
      { weekday: FRIDAY, opens_at: "08:00", closes_at: "12:30" },
      { weekday: 1, opens_at: "09:00", closes_at: "22:00" },
    ];
    const week = weekFromPeriods(saved);

    expect(week[FRIDAY]).toEqual({
      open: true,
      periods: [
        { opens_at: "08:00", closes_at: "12:30" },
        { opens_at: "14:30", closes_at: "02:00" },
      ],
    });
    expect(week[SUNDAY].open).toBe(false);

    // Monday first, closed days left out.
    expect(periodsFromWeek(week)).toEqual([
      { weekday: 1, opens_at: "09:00", closes_at: "22:00" },
      { weekday: FRIDAY, opens_at: "08:00", closes_at: "12:30" },
      { weekday: FRIDAY, opens_at: "14:30", closes_at: "02:00" },
    ]);
  });
});

describe("openingHoursProblems", () => {
  it("accepts late nights and a Friday prayer break", () => {
    expect(
      openingHoursProblems([
        { weekday: FRIDAY, opens_at: "08:00", closes_at: "12:30" },
        { weekday: FRIDAY, opens_at: "14:30", closes_at: "02:00" },
        { weekday: SATURDAY, opens_at: "02:00", closes_at: "10:00" },
      ]),
    ).toEqual([]);
  });

  it("explains an overlap, including a late night running into the next morning", () => {
    expect(
      openingHoursProblems([
        { weekday: FRIDAY, opens_at: "08:00", closes_at: "02:00" },
        { weekday: SATURDAY, opens_at: "01:00", closes_at: "10:00" },
      ]),
    ).toEqual(["Saturday 1:00 am – 10:00 am overlaps Friday 8:00 am – 2:00 am (next day)."]);
  });

  it("sees Saturday night running into Sunday morning", () => {
    expect(
      openingHoursProblems([
        { weekday: SUNDAY, opens_at: "01:00", closes_at: "10:00" },
        { weekday: SATURDAY, opens_at: "20:00", closes_at: "02:00" },
      ]),
    ).toHaveLength(1);
  });

  it("ADR-016 allows at most two periods on one day", () => {
    expect(
      openingHoursProblems([
        { weekday: FRIDAY, opens_at: "08:00", closes_at: "10:00" },
        { weekday: FRIDAY, opens_at: "11:00", closes_at: "12:00" },
        { weekday: FRIDAY, opens_at: "13:00", closes_at: "14:00" },
      ]),
    ).toEqual(["Friday can have at most two opening periods."]);
  });
});
