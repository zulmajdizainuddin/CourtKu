# ADR-001: Separate API and frontend

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

CourtKu needs a web app now and possibly a mobile app later.

## Decision

Build a Laravel REST API (apps/api) and a separate Next.js frontend (apps/web).

## Alternatives considered

- Laravel + Inertia monolith: faster to start, but a mobile app would need a second API later.

## Consequences

- The same API can serve a future mobile app without changes (Goal 4).
- Two apps to deploy; API types are generated from the OpenAPI spec to keep them in sync.
