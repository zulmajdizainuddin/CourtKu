// How the web app proves to the API that a request comes through it (ADR-025).
// The API only believes a visitor's IP and host when this secret comes along, so someone
// calling the API directly can't pretend to be a different visitor to get around rate limits.
// Server-side only: WEB_PROXY_SECRET has no NEXT_PUBLIC_ prefix, so it never reaches the browser.

export const SECRET_HEADER = "x-web-proxy-secret";
export const CLIENT_IP_HEADER = "x-web-client-ip";

/** The visitor's IP. On Vercel the first X-Forwarded-For entry is the real visitor: Vercel overwrites whatever the browser sent. */
export function visitorIp(headers: Headers): string | null {
  const forwarded = headers.get("x-forwarded-for")?.split(",")[0]?.trim();
  return forwarded || headers.get("x-real-ip") || null;
}

/**
 * The headers to send on when a browser request is forwarded to the API: the secret and the visitor's IP.
 * Anything a browser sent under these names is thrown away first. Without a secret nothing is added.
 */
export function forwardedRequestHeaders(incoming: Headers, secret: string | undefined): Headers {
  const headers = new Headers(incoming);
  headers.delete(SECRET_HEADER);
  headers.delete(CLIENT_IP_HEADER);
  if (!secret) return headers;

  headers.set(SECRET_HEADER, secret);
  // Locally there may be no forwarded address; the visitor is then this computer.
  headers.set(CLIENT_IP_HEADER, visitorIp(incoming) ?? "127.0.0.1");
  return headers;
}

/** The header the web app's own server sends when it asks the API for itself (e.g. building a public venue page). */
export function serverRequestHeaders(secret: string | undefined): Record<string, string> {
  return secret ? { [SECRET_HEADER]: secret } : {};
}
