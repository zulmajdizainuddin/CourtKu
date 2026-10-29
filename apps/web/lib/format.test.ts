import { describe, expect, it } from "vitest";
import { formatDate, formatDay, todayInMalaysia } from "./format";

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
