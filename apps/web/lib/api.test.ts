import { describe, expect, it } from "vitest";
import { ApiError, toApiError } from "./api";

describe("toApiError", () => {
  it("reads the standard API error shape, including field messages", () => {
    const err = toApiError(422, {
      error: {
        code: "VALIDATION_FAILED",
        message: "The email has already been taken.",
        details: { fields: { email: ["The email has already been taken."] } },
      },
    });

    expect(err).toBeInstanceOf(ApiError);
    expect(err.status).toBe(422);
    expect(err.code).toBe("VALIDATION_FAILED");
    expect(err.fields.email).toEqual(["The email has already been taken."]);
  });

  it("still gives a friendly error when the body is not our shape (e.g. a proxy error page)", () => {
    const err = toApiError(502, null);

    expect(err.code).toBe("HTTP_502");
    expect(err.message).toMatch(/try again/i);
  });
});
