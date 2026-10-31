# ADR-004: Sanctum SPA cookie auth, with the web app forwarding all API calls

- **Status:** Accepted
- **Date:** 2026-10-09
- **Changed by:** [ADR-025](025-web-app-proves-itself-with-a-secret.md): Laravel only believes the forwarded headers when the web app's secret comes along.

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
- ~~Laravel currently trusts X-Forwarded-* from anyone.~~ Fixed in M5 by ADR-025: the web app sends a shared secret, and only then are the forwarded headers believed.
- Sanctum tokens remain available for a mobile app later.
