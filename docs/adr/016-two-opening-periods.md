# ADR-016: Up to two opening periods per day

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

Some venues close mid-day, e.g. for Friday prayers (12:30 to 14:30).

## Decision

operating_hours allows up to two rows (periods) per weekday. Time between the periods is never bookable.

## Alternatives considered

- One period per day, and owners block the break by hand every week: easy to forget.

## Consequences

- The slot grid is built from each period separately.
- Validation stops periods from overlapping.
