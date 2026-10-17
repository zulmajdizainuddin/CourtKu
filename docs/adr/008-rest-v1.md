# ADR-008: REST with versioned routes under /api/v1

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

The API must be easy to cache, document and test, and must not break a future mobile app.

## Decision

REST resources under /api/v1, one JSON shape for data ({data, meta}) and for errors ({error: {code, message, details}}), documented with Scribe.

## Alternatives considered

- GraphQL: flexible, but harder to cache and more than this problem needs.

## Consequences

- Breaking changes go to /api/v2 while /api/v1 keeps working.
- The health check stays unversioned at /api/health; auth routes live at /auth (ADR-012).
