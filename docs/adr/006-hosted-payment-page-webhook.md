# ADR-006: Hosted payment page, webhook as source of truth

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

Players pay by FPX. CourtKu must never handle bank details, and a browser redirect can be lost or faked.

## Decision

Send players to the gateway's hosted page. Only the signed server-to-server callback confirms a payment; the redirect only improves the experience.

## Alternatives considered

- Embedding bank or card forms: brings PCI scope and risk.

## Consequences

- No payment details touch CourtKu.
- Needs idempotent webhook handling and a reconciliation job (NFR-04).
