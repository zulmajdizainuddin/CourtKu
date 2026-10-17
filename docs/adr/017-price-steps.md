# ADR-017: Prices are set in 10-sen steps

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

Prices are per hour, but bookings use 30-minute cells (ADR-010). An odd number of sen per hour (e.g. RM 25.25 = 2525 sen) cannot be split evenly into two cells.

## Decision

Prices must be multiples of 10 sen (RM 25.00, RM 25.50, RM 25.90), so half an hour is always a whole number of sen.

## Alternatives considered

- Allow any price and round each cell: totals could be a sen off from what the owner expects.

## Consequences

- No rounding anywhere (NFR-10).
- Validation rejects prices that are not 10-sen steps.
