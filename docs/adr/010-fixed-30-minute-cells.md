# ADR-010: Store booked time in fixed 30-minute cells

- **Status:** Accepted
- **Date:** 2026-10-09

## Context

Venues may show 30- or 60-minute slots and may change this later.

## Decision

booking_slots always stores 30-minute cells, and every booking starts on :00 or :30, whatever slot length the venue shows.

## Alternatives considered

- Cells at each venue's own slot length: after a change from 30 to 60 minutes, mixed cell sizes would let overlaps slip past the unique index.

## Consequences

- The unique index stays correct forever.
- A 2-hour booking is always 4 rows.
