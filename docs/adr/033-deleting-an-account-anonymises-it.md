# ADR-033: Deleting an account anonymises it; past bookings stay with the venue

- **Status:** Accepted
- **Date:** 2026-11-12

## Context

NFR-07 (PDPA) and the plan's section 11 say players can delete their account, and that past bookings keep the venue's record while personal fields are anonymised. Bookings point at the user (`bookings.user_id`), and venues' schedules, reports, CSV exports and payment records read the player's name, email and phone through that link.

## Decision

- **`DELETE /api/v1/me` with the password**, from a "Delete account" card on My account → Security. The password is asked again so someone at an unlocked laptop can't do it in one click. Limited to 5 tries a minute.
- **Refused** (409) while it would leave someone stuck:
  - `ACCOUNT_HAS_UPCOMING_BOOKINGS`: the venue expects the player. Cancel first, or wait until the game is over.
  - `ACCOUNT_OWNS_VENUE`: a venue needs an owner. Handing a venue over is not built yet, so the owner contacts CourtKu.
  - `ACCOUNT_IS_ADMIN`: the platform admin is never deleted from the site.
- **What happens** (`App\Services\AccountDeletion`, one transaction): the name becomes "Deleted user", the email `deleted-<id>@deleted.example.com` (which frees the real address for a new sign-up and can never receive mail), the phone is cleared, the password is replaced with a random one nobody knows, two-factor and email verification are cleared, staff memberships, API tokens, password-reset links and login sessions are removed, and the row is soft-deleted, so it can't log in.
- **Bookings keep the link.** `Booking::user()` and `paidBy()` include soft-deleted users, so the venue's schedule, reports and payments show "Deleted user" with no email or phone, and totals don't change.
- A deleted account is never emailed (`NotificationSending` check in `AppServiceProvider`), on top of example.com never being emailed on the live site.

## Alternatives considered

- **Erase the user row and its bookings:** cleanest for privacy, but venues would lose their history and their revenue totals would change after the fact.
- **Set `bookings.user_id` to null:** works, but the booking would then look like a walk-in with no name, which is confusing for staff.
- **Delete without the password:** one less step, but too easy to do by accident or on someone else's laptop.

## Consequences

- Bookings made at the counter or by phone keep the name and phone staff typed (`customer_name`, `customer_phone`); those are the venue's own records, not the account, and the venue is the data user for them.
- Locked backups (ADR-029) still hold the old details for up to 30 days. Restoring an old backup would bring a deleted account back; after a restore, deletions made since that backup must be done again.
- An owner can't delete their account until venue hand-over or closing exists.
