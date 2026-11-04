# ADR-032: Terms, Privacy Notice and a refund policy built from the cancel setting

- **Status:** Accepted
- **Date:** 2026-11-12

## Context

FR-40 asks for a Terms of Use, a Privacy Notice, and each venue's cancellation and refund policy shown before payment. The plan asks for English and Bahasa Melayu; the owner chose English only for now.

Venues already have a cancel cutoff (`cancel_cutoff_hours`, FR-24): players cancel online until 2 to 48 hours before the start, after which only the venue can cancel. Refunds are made by the venue in Stripe and recorded in CourtKu (FR-25). Before this, the booking page said only "Free cancellation online until X hours before the start."

## Decision

- **`/terms` and `/privacy`**, static pages in plain English, linked from a footer on every page and from the sign-up form ("By creating an account you agree to..."). No tick box: signing up is the agreement, as on most booking sites, and there is nothing extra to store.
- The Terms say plainly that the live site is a demonstration: payments run in Stripe test mode and venues marked as demo are made up. Each booking is an agreement between the player and the venue; CourtKu provides the system and never holds the money (ADR-024).
- The Privacy Notice follows the PDPA 2010 summary in the plan (section 11): what is collected and why, who sees it (the booked venue is the data user, CourtKu processes for it), the service providers, transfer outside Malaysia (servers in Singapore), how long data is kept (account until deleted, backups 30 days, ADR-029), cookies, and rights including deleting the account (ADR-033).
- **The refund policy is written by CourtKu from the venue's settings**, so it always matches how cancelling actually works: free online cancellation until the cutoff; with online payment, a full refund when cancelled in time and none for a no-show; after the cutoff, contact the venue. The owner can add an optional note of up to 300 characters (`venues.refund_note`, on the Settings tab), shown under the rule as "From the venue: ...".
- The policy is shown on the venue page (beside the opening hours) and in the booking step, above the Pay and Book buttons, so it is read before paying.

## Alternatives considered

- **The owner writes the whole policy:** flexible, but it could be empty, or promise something the cancel setting doesn't do (e.g. "cancel any time").
- **The rule only, no owner text:** simplest, but leaves no room for venue rules like rain on outdoor courts.
- **A required "I agree" tick box with a stored time:** stronger proof of consent, but one more step at sign-up for little gain on a demo.
- **Bahasa Melayu versions now:** wanted by the plan, but a second language for legal text needs care to get right; left for later with FR-41.

## Consequences

- The policy can't drift from the real cancel rule: changing the cutoff changes the text everywhere.
- The legal text is a reasonable summary, not legal advice. Before charging real venues (M6), it should be checked by a lawyer, and translated into Bahasa Melayu.
- The contact address on the pages is CourtKu's sender email (courtku9@gmail.com) until there is a domain.
