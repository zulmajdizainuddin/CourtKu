# ADR-007: Monorepo with apps/api and apps/web

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

The API and web app change together, and a reviewer should find everything in one place.

## Decision

Keep both apps, docs and CI in one repository: apps/api, apps/web, docs/, .github/.

## Alternatives considered

- Two repositories: cleaner separation, but issues, CI and docs are split.

## Consequences

- One place for issues, CI and decisions.
- CI runs a separate job per app.
