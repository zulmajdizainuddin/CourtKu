# ADR-024: Venues connect their Stripe accounts through Stripe Connect; CourtKu never holds their keys

- **Status:** Accepted
- **Date:** 2026-11-05
- **Builds on:** [ADR-009](009-venue-own-gateway-account.md) (each venue's own account) and [ADR-023](023-stripe-test-mode-instead-of-billplz.md) (Stripe test mode)

## Context

ADR-009 says each venue's payments go to the venue's own gateway account, and the plan stored each venue's API keys encrypted in CourtKu. The simplest way to do that with Stripe is to ask the owner to paste their Stripe secret key.

A Stripe secret key is a master key to the account: it can read customers and payments, make refunds and create charges. If CourtKu held every venue's key, one breach of CourtKu (its database plus its `APP_KEY`) would hand an attacker every venue's Stripe account. Owners would also have to copy a secret around, and a key changed in Stripe would quietly break CourtKu.

## Decision

- CourtKu is a **Stripe Connect platform**. It holds exactly one secret, its own platform key (`STRIPE_SECRET_KEY`), in the server's settings only.
- **Connect with Stripe:** CourtKu asks Stripe to open an account for the venue and keeps only its id (`acct_...`, not a secret). The owner gives their details and bank account to Stripe on Stripe's own sign-up pages, then comes back to the Payments tab.
- The account is the venue's own in every way that matters (Stripe Accounts v2, which Stripe now requires for new platforms): full Stripe dashboard for the owner, Stripe takes its fees from the venue, and Stripe, not CourtKu, covers refunds and disputes.
- Payments are **direct charges**: CourtKu opens the payment page *on the venue's account* (the `Stripe-Account` header), so the money lands with the venue (ADR-009 holds).
- Online payment is offered only once Stripe says the account can take card payments.
- Disconnecting only makes CourtKu stop using the account; the Stripe account stays the venue's.

## Alternatives considered

- **The owner pastes their secret key** (built first, never merged): one breach of CourtKu would expose every venue.
- **The owner pastes a restricted key** with only the permissions CourtKu needs: less damage if leaked, but still a secret per venue in CourtKu's database.
- **CourtKu collects all money and pays venues out** (Connect "marketplace" style): CourtKu would hold other people's money, which ADR-009 rules out.

## Consequences

- No venue secrets in CourtKu's database: `venue_payment_accounts` has no column for one.
- One webhook for all venues: Stripe's Connect webhook names the venue's account in each event, and is checked with one signing secret (M4 PR 4).
- CourtKu's own Stripe account must have Connect turned on. In test mode this needed no business registration.
- FPX on a venue's account needs the venue's business type from Stripe's sign-up; until then the demo pays by test card.
