# ADR-005: One row per booked slot with a unique index

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

Two players pressing Book at the same moment must never both get the same court.

## Decision

Every booked or blocked 30-minute cell is a row in booking_slots with a unique index on (court_id, slot_start). A booking inserts its rows inside a transaction; a duplicate insert fails and the API returns 409 SLOT_TAKEN.

## Alternatives considered

- Checking overlaps in application code: two requests can both see the slot as free and both succeed.

## Consequences

- The database rejects double bookings even at the same millisecond (NFR-01).
- Holds and cancellations must add and delete booking_slots rows consistently.
