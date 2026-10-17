# ADR-004: Sanctum SPA cookie auth, with the web app forwarding all API calls

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

Logged-in sessions must be safe from XSS, and there is no paid domain to share between the web app and the API.

## Decision

The browser only talks to the Next.js app. Next.js rewrites forward /api/*, /auth/* and /sanctum/csrf-cookie to Laravel, so Laravel's HttpOnly session cookie is first-party. Laravel trusts the proxy's X-Forwarded-* headers, so it sees the visitor's IP and the web app's host.

## Alternatives considered

- JWT stored in the browser: readable by any XSS bug.
- API on a subdomain of a shared parent domain: needs a paid domain and CORS setup.

## Consequences

- JavaScript never sees a token; no CORS configuration.
- Every browser-facing API path must be listed in next.config.ts rewrites.
- Laravel currently trusts X-Forwarded-* from anyone. Before going live (M5) the API must only accept requests that come through the proxy, or a direct caller could fake their IP.
- Sanctum tokens remain available for a mobile app later.
