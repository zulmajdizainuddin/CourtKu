# ADR-023: Online payments use Stripe in test mode, not the Billplz Sandbox

- **Status:** Accepted
- **Date:** 2026-11-05
- **Changes:** the gateway named in the plan (section 9) and in [ADR-009](009-venue-own-gateway-account.md). The rest of ADR-009 (each venue's own account, keys encrypted and never returned) still holds.

## Context

The plan builds M4 against the Billplz Sandbox: Malaysian, ringgit only, FPX online banking, signed callbacks. Signing up for Billplz asks for real business details, including an SSM registration number and a bank account in the business's name. CourtKu is a portfolio project run by one person without a registered business, so it can't get a Billplz account, and entering made-up registration details is not an option.

## Decision

- Online payments go through **Stripe Checkout**, in **test mode**, with a Stripe account whose country is Malaysia. Test mode needs only an email sign-up; no business check until real money is taken.
- The booking code talks only to a `PaymentGateway` interface (`app/Payments`). `StripeGateway` is the real one; `FakeGateway` is for tests. Billplz or ToyyibPay can be added later as one more class.
- The player pays on Stripe's own page in ringgit (MYR) (ADR-006). CourtKu doesn't list payment methods: the page shows what the venue's Stripe account allows. Today, in the sandbox, that is cards (e.g. the test card 4242 4242 4242 4242); FPX appears on the same page once an account is activated, with no code change.
- Webhooks are checked with Stripe's signature (`Stripe-Signature`: HMAC-SHA256 of "time.body" with the endpoint's secret, at most 5 minutes old). The test for it uses a signature worked out separately, not by the code under test.
- Stripe keeps a payment page open for at least 30 minutes, longer than CourtKu's 15-minute hold. CourtKu closes the page itself when the hold runs out (the plan's "delete the Bill").

## Alternatives considered

- **Billplz with made-up business details:** dishonest, and the account would be blocked at verification.
- **ToyyibPay sandbox:** Malaysian and open to individuals, but its sandbox sign-up and callback signing were unclear.
- **No real gateway, `FakeGateway` only:** no real payment could ever be shown on the live demo.

## Consequences

- The live demo takes test payments end to end, and no real money can move: `courtku:stripe-try` refuses anything but a test key.
- FPX can't be shown until a Stripe account is activated with a business registration; the demo uses test cards until then.
- A real venue (M6) needs its own Stripe account, or stays on pay at the counter until then.
- Stripe charges per payment once live; Billplz's lower FPX fee could matter to a real venue later, which the interface keeps open.
