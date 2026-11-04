import { describe, expect, it } from "vitest";
import { mainSport, stockPhoto } from "./stock-photo";

describe("stockPhoto", () => {
  it("gives a cropped Unsplash photo of the size asked for", () => {
    const url = stockPhoto("futsal", "demo-goal-arena-shah-alam", 600, 400);
    expect(url).toMatch(/^https:\/\/images\.unsplash\.com\/photo-[\w-]+\?w=600&h=400&fit=crop/);
  });

  it("uses the venue's sport, or badminton when it doesn't know the sport", () => {
    const futsal = stockPhoto("futsal", "a", 600, 400);
    const pickleball = stockPhoto("pickleball", "a", 600, 400);
    const badminton = stockPhoto("badminton", "a", 600, 400);
    expect(new Set([futsal, pickleball, badminton]).size).toBe(3);
    expect(stockPhoto(null, "a", 600, 400)).toBe(badminton);
    expect(stockPhoto("squash", "a", 600, 400)).toBe(badminton);
  });

  it("always gives the same venue the same photo, and varies it between venues", () => {
    expect(stockPhoto("badminton", "demo-smash-centre-kepong", 600, 400)).toBe(stockPhoto("badminton", "demo-smash-centre-kepong", 600, 400));
    const slugs = ["demo-shuttle-house-cheras", "demo-smash-centre-kepong", "demo-net-play-puchong", "demo-jb-smash-arena", "demo-ipoh-racket-club"];
    expect(new Set(slugs.map((s) => stockPhoto("badminton", s, 600, 400))).size).toBeGreaterThan(1);
  });
});

describe("mainSport", () => {
  it("picks the sport most courts offer, alphabetical on a tie", () => {
    expect(mainSport([{ sports: ["futsal"] }, { sports: ["badminton"] }, { sports: ["badminton"] }])).toBe("badminton");
    expect(mainSport([{ sports: ["pickleball"] }, { sports: ["futsal"] }])).toBe("futsal");
    expect(mainSport([])).toBeNull();
  });
});
