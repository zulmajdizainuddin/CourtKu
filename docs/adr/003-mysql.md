# ADR-003: MySQL 8

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

We need a relational database with transactions, unique indexes and CHECK constraints.

## Decision

Use MySQL 8 (8.4 LTS locally and in CI).

## Alternatives considered

- PostgreSQL: excellent, but nothing in this design needs it.

## Consequences

- Familiar, and the most common database in Malaysian job ads.
- The slot-table design (ADR-005) needs no Postgres-only features.
