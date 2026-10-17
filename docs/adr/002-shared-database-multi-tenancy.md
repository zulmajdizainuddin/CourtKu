# ADR-002: Multi-tenancy with one shared database and a venue_id column

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

Many venues use one CourtKu installation, and each must only see its own data.

## Decision

Use one database. Every table that belongs to a venue has a venue_id column. Tenant checks are written once (Policies, scoped route binding) and tested on every manage endpoint.

## Alternatives considered

- One database per venue: strong isolation, but much harder to migrate and run on a free server.

## Consequences

- Simple to run and migrate; enough for hundreds of venues.
- A missing venue check would leak data, so the tenant isolation test suite is mandatory.
