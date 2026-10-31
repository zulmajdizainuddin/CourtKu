import { afterEach, describe, expect, it, vi } from "vitest";
import { ApiError, api, toApiError } from "./api";

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

describe("toApiError details", () => {
  it("FR-38 keeps the bookings in the way, so the form can list them", () => {
    const err = toApiError(409, {
      error: {
        code: "BOOKINGS_CONFLICT",
        message: "1 upcoming booking is on these days.",
        details: { bookings: [{ code: "CK-7F3K2" }] },
      },
    });

    expect(err.code).toBe("BOOKINGS_CONFLICT");
    expect(err.details.bookings).toEqual([{ code: "CK-7F3K2" }]);
  });
});

describe("api", () => {
  afterEach(() => vi.unstubAllGlobals());

  /** Records what api() hands to fetch, and answers 201. */
  function recordFetch() {
    const fetchMock = vi.fn().mockResolvedValue(new Response(JSON.stringify({ data: {} }), { status: 201 }));
    vi.stubGlobal("fetch", fetchMock);
    document.cookie = "XSRF-TOKEN=test-token"; // already have a CSRF cookie, so no extra request
    return () => fetchMock.mock.calls[0][1] as RequestInit & { headers: Record<string, string> };
  }

  it("sends objects as JSON, with the CSRF token", async () => {
    const sent = recordFetch();

    await api("/api/v1/manage/venues", { method: "POST", body: { name: "Arena" } });

    expect(sent().headers["Content-Type"]).toBe("application/json");
    expect(sent().headers["X-XSRF-TOKEN"]).toBe("test-token");
    expect(sent().body).toBe('{"name":"Arena"}');
  });

  it("sends a photo as a file upload and lets the browser set its Content-Type", async () => {
    const sent = recordFetch();
    const form = new FormData();
    form.append("photo", new Blob(["fake image"], { type: "image/jpeg" }), "court.jpg");

    await api("/api/v1/manage/venues/1/photos", { method: "POST", body: form });

    expect(sent().headers["Content-Type"]).toBeUndefined();
    expect(sent().body).toBe(form);
  });
});
