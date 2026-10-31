# ADR-022: Timed jobs run lazily and from a cron-called endpoint

- **Status:** Accepted
- **Date:** 2026-11-04

## Context

Some things must happen at a time, not when someone clicks:

- an unpaid checkout hold must give its half hours back when the venue's hold time runs out (FR-16);
- a checked-in booking becomes completed when its time is over;
- later in M4, payments still pending must be checked with the gateway every 15 minutes (NFR-04).

Laravel's scheduler does this with `php artisan schedule:work` or a cron entry on the server. The API runs on Render's free plan, which has no always-on worker, no cron and no shell. Render's own Cron Jobs are a paid feature.

## Decision

Two layers, so correctness never depends on a timer:

1. **Lazy release.** Whatever needs to know which half hours are free (the availability grid, a quote, a booking, the staff schedule, the FR-38 checks, a player's bookings) first expires the holds whose time is up (`App\Services\Holds`). A player never sees, or is blocked by, a hold that has run out, even if no timer ever ran.
2. **A tick every minute** for the rest (`App\Services\Housekeeping`, `php artisan courtku:tick`):
   - on a server with a scheduler, `routes/console.php` runs it every minute;
   - on Render's free plan, a free cron service ([cron-job.org](https://cron-job.org)) calls `POST /api/tick` every minute with the header `X-Cron-Secret`. Without the right secret the endpoint answers 403; with no `CRON_SECRET` set it answers 404.

The endpoint runs the jobs inside the request instead of starting `schedule:run`, which would start new PHP processes from inside the web server. Every status change still goes through `BookingStateMachine` (row lock + audit log), so a tick that runs twice, late, or at the same moment as a payment changes each booking at most once.

## Alternatives considered

- **Render Cron Job:** the simplest, but paid.
- **GitHub Actions on a schedule:** free, but runs every 5 minutes at best and is often late by much more.
- **A timer only, with no lazy release:** a missed or late tick would keep courts looking booked.
- **Moving to an always-on server now (Oracle Cloud with Coolify, a paid Render plan):** the plan's first choice, but sign-up failed (see deploy notes). The same image runs there with `schedule:work` and no code change.

## Consequences

- Holds are always released on time from a player's point of view; the tick only tidies up and, from M4 PR 6, cancels the gateway's payment page and reconciles payments.
- The cron service holds a secret (`CRON_SECRET`), set in Render and in the cron job's header; it can only run the tick, nothing else.
- If the cron service stops, players are unaffected, but finished bookings stay checked in and pending payments wait for their callback until it runs again.
