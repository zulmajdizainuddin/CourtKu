import { describe, expect, it } from "vitest";
import { safeNextPath } from "./safe-next-path";

describe("safeNextPath", () => {
  it("keeps paths on our own site", () => {
    expect(safeNextPath("/me")).toBe("/me");
    expect(safeNextPath("/dashboard/3/schedule?date=2026-11-02")).toBe("/dashboard/3/schedule?date=2026-11-02");
  });

  it("falls back to the home page for anything else", () => {
    expect(safeNextPath(null)).toBe("/");
    expect(safeNextPath("https://evil.example")).toBe("/");
    expect(safeNextPath("//evil.example")).toBe("/");
    expect(safeNextPath("/\\evil.example")).toBe("/");
  });
});
