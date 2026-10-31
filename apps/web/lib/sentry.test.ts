import { describe, expect, it } from "vitest";
import { sentryOptions } from "./sentry";

describe("sentryOptions", () => {
  it("sends nothing about the visitor: no user, cookies, headers, bodies, query strings or code values", () => {
    expect(sentryOptions("production").dataCollection).toEqual({
      userInfo: false,
      cookies: false,
      httpHeaders: false,
      httpBodies: [],
      urlQueryParams: false,
      databaseQueryData: false,
      queues: false,
      stackFrameVariables: false,
    });
  });

  it("turns on no performance tracing or session replay", () => {
    const options = sentryOptions("production");
    expect(options).not.toHaveProperty("tracesSampleRate");
    expect(options).not.toHaveProperty("replaysSessionSampleRate");
  });

  it("says development on a laptop", () => {
    expect(sentryOptions(undefined).environment).toBe("development");
  });
});
