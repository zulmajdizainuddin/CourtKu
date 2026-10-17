# ADR-014: A court can offer more than one sport

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

Some venues switch one court between sports, e.g. futsal in the evening and badminton in the morning.

## Decision

Replace courts.sport_id with a court_sports link table (court_id, sport_id). A court still has one booking_slots timeline, so bookings for different sports on the same court can never overlap.

## Alternatives considered

- One sport per court: simpler, but owners would create duplicate courts that could double-book the same floor.

## Consequences

- Each booking records which sport is played, for reports.
- Availability filtered by sport shows every court offering that sport.
- Pricing per court and sport can be added later if venues need it.
