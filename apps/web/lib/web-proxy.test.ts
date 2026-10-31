import { describe, expect, it } from "vitest";
import { CLIENT_IP_HEADER, SECRET_HEADER, forwardedRequestHeaders, serverRequestHeaders, visitorIp } from "./web-proxy";

describe("visitorIp", () => {
  it("takes the first X-Forwarded-For address, which Vercel sets to the real visitor", () => {
    expect(visitorIp(new Headers({ "x-forwarded-for": "60.50.40.30, 76.76.21.21" }))).toBe("60.50.40.30");
  });

  it("falls back to X-Real-IP, then to nothing", () => {
    expect(visitorIp(new Headers({ "x-real-ip": "60.50.40.30" }))).toBe("60.50.40.30");
    expect(visitorIp(new Headers())).toBeNull();
  });
});

describe("forwardedRequestHeaders", () => {
  it("adds the secret and the visitor's IP, keeping the browser's other headers", () => {
    const headers = forwardedRequestHeaders(
      new Headers({ "x-forwarded-for": "60.50.40.30", cookie: "courtku-session=abc" }),
      "s3cret",
    );

    expect(headers.get(SECRET_HEADER)).toBe("s3cret");
    expect(headers.get(CLIENT_IP_HEADER)).toBe("60.50.40.30");
    expect(headers.get("cookie")).toBe("courtku-session=abc");
  });

  it("replaces whatever a browser sent under the same names", () => {
    const headers = forwardedRequestHeaders(
      new Headers({ "x-forwarded-for": "60.50.40.30", [SECRET_HEADER]: "guess", [CLIENT_IP_HEADER]: "1.1.1.1" }),
      "s3cret",
    );

    expect(headers.get(SECRET_HEADER)).toBe("s3cret");
    expect(headers.get(CLIENT_IP_HEADER)).toBe("60.50.40.30");
  });

  it("adds nothing without a secret, and still drops what a browser made up", () => {
    const headers = forwardedRequestHeaders(new Headers({ [SECRET_HEADER]: "guess", [CLIENT_IP_HEADER]: "1.1.1.1" }), undefined);

    expect(headers.has(SECRET_HEADER)).toBe(false);
    expect(headers.has(CLIENT_IP_HEADER)).toBe(false);
  });

  it("uses this computer's address locally, when nothing was forwarded", () => {
    expect(forwardedRequestHeaders(new Headers(), "s3cret").get(CLIENT_IP_HEADER)).toBe("127.0.0.1");
  });
});

describe("serverRequestHeaders", () => {
  it("sends only the secret, so the API knows no visitor is behind the request", () => {
    expect(serverRequestHeaders("s3cret")).toEqual({ [SECRET_HEADER]: "s3cret" });
    expect(serverRequestHeaders(undefined)).toEqual({});
  });
});
