import { describe, expect, it } from "vitest";
import { formatDate } from "./format";

describe("formatDate", () => {
  it("NFR-12 shows the date in Malaysia time, not UTC", () => {
    // 16:30 UTC is 00:30 the next day in Malaysia (UTC+8).
    expect(formatDate("2026-10-29T16:30:00+00:00")).toBe("30 Oct 2026");
  });
});
