import { afterEach, describe, expect, it, vi } from "vitest";
import { GET } from "./route";

describe("GET /version", () => {
  afterEach(() => vi.unstubAllEnvs());

  it("says which commit Vercel built", async () => {
    vi.stubEnv("VERCEL_GIT_COMMIT_SHA", "abc123");

    expect(await GET().json()).toEqual({ version: "abc123" });
  });

  it("says local on a laptop", async () => {
    vi.stubEnv("VERCEL_GIT_COMMIT_SHA", undefined);

    expect(await GET().json()).toEqual({ version: "local" });
  });
});
