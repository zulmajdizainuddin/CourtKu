# ADR-009: Each venue connects its own payment gateway account

- **Status:** Accepted
- **Date:** 2026-10-09
- **Changed by:** [ADR-023](023-stripe-test-mode-instead-of-billplz.md): the gateway is Stripe, not Billplz. Each venue still connects its own account.
- **Changed by:** [ADR-024](024-venues-connect-through-stripe-connect.md): venues connect through Stripe Connect, so CourtKu stores no keys of theirs.

## Context

Collecting players' money and paying venues out would make CourtKu hold other people's money.

## Decision

Each venue connects its own Billplz account. Its keys are stored encrypted (Laravel encrypted cast) and never returned by the API.

## Alternatives considered

- Platform collects all payments and pays venues out: payout, refund and regulatory work.

## Consequences

- Money settles straight to the venue.
- Each venue needs its own gateway account; until then it uses pay-at-counter.
