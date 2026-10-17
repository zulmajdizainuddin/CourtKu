# ADR-013: Protected pages ask the API who is logged in, not the cookie

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

The plan had a Next.js route guard (middleware, now called proxy in Next.js 16) that redirected visitors without a session cookie. Laravel gives every visitor a session cookie, logged in or not, so the cookie's presence proves nothing.

## Decision

Protected pages wrap their content in RequireUser, which calls GET /api/v1/me and redirects to /login?next=... when nobody is logged in. After login, safeNextPath() only allows paths on our own site.

## Alternatives considered

- A proxy that calls the API on every request: slow, and Next.js advises against fetching data in the proxy.

## Consequences

- Correct behaviour for one extra API call (cached by TanStack Query).
- The API still enforces every permission; the guard only decides what the page shows.
