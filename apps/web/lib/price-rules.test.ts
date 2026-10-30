import { describe, expect, it } from "vitest";
import { formatRuleTimes, formatWeekdays } from "./price-rules";

describe("formatWeekdays", () => {
  it.each([
    [[1, 2, 3, 4, 5], "Mon–Fri"],
    [[6, 0], "Sat, Sun"],
    [[0, 1, 2, 3, 4, 5, 6], "Every day"],
    [[1, 3, 5], "Mon, Wed, Fri"],
    [[5, 6, 0], "Fri–Sun"],
    [[1, 2, 4, 5, 6], "Mon, Tue, Thu–Sat"],
  ])("writes %j as %s, the same as the API", (weekdays, text) => {
    expect(formatWeekdays(weekdays)).toBe(text);
  });
});

describe("formatRuleTimes", () => {
  it("says midnight and (next day) the same way as opening hours", () => {
    expect(formatRuleTimes({ starts_at: "18:00", ends_at: "00:00", ends_next_day: true })).toBe("6:00 pm – midnight");
    expect(formatRuleTimes({ starts_at: "18:30", ends_at: "02:00", ends_next_day: true })).toBe(
      "6:30 pm – 2:00 am (next day)",
    );
    expect(formatRuleTimes({ starts_at: "08:00", ends_at: "12:00", ends_next_day: false })).toBe("8:00 am – 12:00 pm");
  });
});
