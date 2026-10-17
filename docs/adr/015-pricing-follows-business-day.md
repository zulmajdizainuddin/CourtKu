# ADR-015: Pricing rules follow the venue's business day

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

A venue open until 2:00 am has a Friday night peak price. By the calendar, 00:00 to 02:00 is already Saturday.

## Decision

Pricing rules and the slot grid use the venue's business day: a Friday rule covers Friday's opening hours, including the hours after midnight. Bookings still store real UTC times.

## Alternatives considered

- Follow the calendar date: easier to code, but surprising for owners and players.

## Consequences

- Matches how owners think about their prices.
- Price calculation needs the business date, not just the timestamp; tests cover 23:30 and 00:30.
