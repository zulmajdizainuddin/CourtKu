import { describe, expect, it } from "vitest";
import { formatDate, formatDay, formatDayTime, hourInMalaysia, todayInMalaysia } from "./format";

describe("formatDate", () => {
  it("NFR-12 shows the date in Malaysia time, not UTC", () => {
    // 16:30 UTC is 00:30 the next day in Malaysia (UTC+8).
    expect(formatDate("2026-10-29T16:30:00+00:00")).toBe("30 Oct 2026");
  });
});

describe("formatDay", () => {
  it("shows a calendar day exactly as it is, never a day earlier or later", () => {
    expect(formatDay("2027-03-30")).toBe("30 Mar 2027");
  });
});

describe("todayInMalaysia", () => {
  it("is already tomorrow in Malaysia late at night UTC", () => {
    expect(todayInMalaysia(new Date("2026-10-31T17:00:00Z"))).toBe("2026-11-01");
    expect(todayInMalaysia(new Date("2026-10-31T15:00:00Z"))).toBe("2026-10-31");
  });
});

describe("hourInMalaysia", () => {
  it("gives the hour in Malaysia, 0 to 23", () => {
    expect(hourInMalaysia(new Date("2026-11-06T16:40:00Z"))).toBe(0); // 00:40 on Saturday there
    expect(hourInMalaysia(new Date("2026-11-06T10:00:00Z"))).toBe(18);
  });
});

describe("formatDayTime", () => {
  it("writes a Malaysia time as day and time, without shifting it", () => {
    expect(formatDayTime("2026-11-02T18:00:00+08:00")).toBe("2 Nov 2026, 6:00 pm");
    expect(formatDayTime("2026-11-07T00:30:00+08:00")).toBe("7 Nov 2026, 12:30 am");
  });
});
