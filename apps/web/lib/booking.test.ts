import { describe, expect, it } from "vitest";
import {
  type Availability,
  bookingDates,
  cellLabel,
  dateChip,
  endLabel,
  lengthOptions,
  startOptions,
  venueSports,
} from "./booking";

/** Half hours on Friday 6 Nov 2026, priced in sen, e.g. cell("18:00", 1500). */
function cell(time: string, price_sen = 1000, available = true) {
  const nextDay = time < "06:00";
  return { starts_at: `2026-11-0${nextDay ? 7 : 6}T${time}:00+08:00`, time, price_sen, available };
}

function availability(courts: Availability["courts"]): Availability {
  return { date: "2026-11-06", closed_reason: null, slot_minutes: 60, min_minutes: 60, max_minutes: 180, courts };
}

const court = (id: number, cells: ReturnType<typeof cell>[]) => ({
  id,
  name: `Court ${id}`,
  is_indoor: true,
  sports: ["badminton"],
  cells,
});

describe("bookingDates", () => {
  it("FR-13 offers today and the venue's days ahead, across a month end", () => {
    const dates = bookingDates("2026-10-30", 7);

    expect(dates).toHaveLength(8);
    expect(dates[0]).toBe("2026-10-30");
    expect(dates[2]).toBe("2026-11-01");
    expect(dates[7]).toBe("2026-11-06");
  });
});

describe("dateChip and lengthOptions", () => {
  it("writes the day and date, and offers the shortest to longest booking in slots", () => {
    expect(dateChip("2026-11-06")).toEqual({ day: "Fri", date: "6 Nov" });
    expect(lengthOptions({ slot_minutes: 60, min_minutes: 60, max_minutes: 180 })).toEqual([60, 120, 180]);
    expect(lengthOptions({ slot_minutes: 30, min_minutes: 60, max_minutes: 120 })).toEqual([60, 90, 120]);
  });
});

describe("venueSports", () => {
  it("lists each sport once, in the usual order", () => {
    expect(venueSports([{ sports: ["pickleball"] }, { sports: ["futsal", "badminton"] }, { sports: ["badminton"] }])).toEqual([
      "badminton",
      "futsal",
      "pickleball",
    ]);
  });
});

describe("cellLabel", () => {
  it("ADR-015 marks the hours after midnight as the next day", () => {
    expect(cellLabel(cell("18:00"), "2026-11-06")).toBe("6:00 pm");
    expect(cellLabel(cell("00:30"), "2026-11-06")).toBe("12:30 am (next day)");
  });
});

describe("endLabel", () => {
  it("says when the booking ends, midnight included", () => {
    expect(endLabel("18:00", 120)).toBe("8:00 pm");
    expect(endLabel("23:00", 60)).toBe("midnight");
    expect(endLabel("23:30", 90)).toBe("1:00 am");
  });
});

describe("startOptions", () => {
  it("adds up each half hour of the booking, peak ones included", () => {
    const options = startOptions(
      availability([court(1, [cell("17:00"), cell("17:30"), cell("18:00", 1500), cell("18:30", 1500)])]),
      60,
    );

    expect(options.map((o) => [o.label, o.courts[0].price_sen])).toEqual([
      ["5:00 pm", 2000],
      ["5:30 pm", 2500],
      ["6:00 pm", 3000],
    ]);
  });

  it("never runs a booking across a break or past closing", () => {
    // Friday: open until 12:30, then from 14:30 (the prayer break).
    const options = startOptions(availability([court(1, [cell("11:30"), cell("12:00"), cell("14:30"), cell("15:00")])]), 60);

    expect(options.map((o) => o.label)).toEqual(["11:30 am", "2:30 pm"]);
  });

  it("lists each start time once, with every court free then", () => {
    const options = startOptions(
      availability([
        court(1, [cell("18:00"), cell("18:30"), cell("19:00")]),
        court(2, [cell("18:30"), cell("19:00")]),
        court(3, [cell("18:00"), cell("18:30", 1000, false)]), // taken (M3)
      ]),
      60,
    );

    expect(options.map((o) => [o.label, o.courts.map((c) => c.id)])).toEqual([
      ["6:00 pm", [1]],
      ["6:30 pm", [1, 2]],
    ]);
  });

  it("ADR-015 keeps Friday night going after midnight", () => {
    const options = startOptions(availability([court(1, [cell("23:30"), cell("00:00"), cell("00:30")])]), 60);

    expect(options.map((o) => o.label)).toEqual(["11:30 pm", "12:00 am (next day)"]);
  });
});
