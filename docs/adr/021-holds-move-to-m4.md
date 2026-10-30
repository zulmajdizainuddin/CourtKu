# ADR-021: Checkout holds arrive with online payment (M4), not in M3

- **Status:** Accepted
- **Date:** 2026-11-01

## Context

The plan builds checkout holds in M3: a booking waits as `pending_payment` for the venue's hold time (default 15 minutes) while the player pays, and a job runs every minute to expire unpaid holds and free their half hours (FR-16).

Online payment (FPX through Billplz) only arrives in M4. Until then, every booking is paid at the venue.

## Decision

- In M3 every booking is **paid at the venue** and is **confirmed straight away**. Staff mark it paid at the counter.
- Holds, the `pending_payment` and `expired` statuses, the expiry job and the 2-holds-per-player limit are built in M4, together with the payment that needs them.
- M3 keeps everything that stops double booking: `booking_slots` with its unique index on (court_id, slot_start), the transaction, `SLOT_TAKEN`, and the state machine with row locks.

## Alternatives considered

- **Build holds in M3 as planned:** they would sit unused for a whole milestone, and couldn't be tested end to end without a payment to wait for.

## Consequences

- M3 can be finished and used by a venue that takes cash or QR at the counter.
- M4 adds two statuses (`pending_payment`, `expired`) to `BookingStatus` and a scheduled job; the booking engine itself doesn't change.
- The scheduler that releases holds must run on the hosting chosen in M4 (Render free has no always-on worker); this is decided there.
