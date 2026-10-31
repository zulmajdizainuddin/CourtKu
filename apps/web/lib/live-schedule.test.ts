import { describe, expect, it } from "vitest";
import { newPlayerBookings } from "./live-schedule";

const online = (code: string) => ({ code, source: "online" as const });
const walkIn = (code: string) => ({ code, source: "walk_in" as const });

describe("newPlayerBookings", () => {
  it("finds online bookings that weren't there before", () => {
    expect(newPlayerBookings([online("CK-AAAAA")], [online("CK-AAAAA"), online("CK-BBBBB")])).toEqual([online("CK-BBBBB")]);
  });

  it("leaves out walk-ins and phone bookings: staff typed those in", () => {
    expect(newPlayerBookings([], [walkIn("CK-CCCCC"), { code: "CK-DDDDD", source: "phone" as const }])).toEqual([]);
  });

  it("finds nothing on the first load, or when a booking was only cancelled", () => {
    expect(newPlayerBookings(undefined, [online("CK-AAAAA")])).toEqual([]);
    expect(newPlayerBookings([online("CK-AAAAA"), online("CK-BBBBB")], [online("CK-AAAAA")])).toEqual([]);
  });
});
