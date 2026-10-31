# ADR-027: Booking emails wait in a database queue and the minute tick sends them

- **Status:** Accepted
- **Date:** 2026-11-07
- **Builds on:** [ADR-022](022-timed-jobs-without-a-scheduler.md) (the every-minute tick)

## Context

M5 adds emails about bookings (FR-26, FR-27): "Booked" and "Cancelled" for the player, and "New booking" and "Cancelled by the player" for the venue's owners and staff. One booking can mean three or four emails.

Until now every email was sent inside the request (`QUEUE_CONNECTION=sync`), because Render's free plan has no background worker. Sending through Brevo takes about a second per email, so a player pressing **Book** would wait several seconds. If Brevo is down or slow, the request fails after the booking is already saved, and the email is lost.

## Decision

- Booking emails (and the payment-problem email from M4) are queued notifications. `App\Services\BookingEmails` queues them once the booking change is saved (`DB::afterCommit`). Nothing is queued for a change that is rolled back, and failing to queue is logged, never undoing the booking.
- The queue lives in MySQL (`QUEUE_CONNECTION=database`, the `jobs` table), so nothing new needs hosting.
- The minute tick (`Housekeeping`, ADR-022) works through the queue last: at most 50 jobs and 20 seconds per tick, so the cron call stays short. The tick's answer says how many went (`emails_sent`).
- A failed email is tried again 1 and 5 minutes later. After 3 failures it moves to `failed_jobs`, where `php artisan queue:failed` lists it and `php artisan queue:retry all` sends it again.
- Account emails (verify your email, reset your password, staff invites) are still sent at once: the person is waiting for them on the screen.

## Alternatives considered

- **Keep sending inside the request:** simplest, but booking gets slower with every email, and a Brevo outage breaks booking.
- **A queue worker on its own server:** the right answer on paid hosting (Render Background Worker), but not free.
- **Brevo's HTTP API with a background `fetch` after the response:** still loses emails when Brevo fails, and PHP-FPM keeps the request's process busy anyway.

## Consequences

- Booking is as fast as before, whatever the email server does.
- Emails arrive up to about a minute late, and only while cron-job.org keeps calling the tick. If the tick stops, emails pile up in `jobs` and all go out once it runs again.
- An email shows the booking as it is when it is sent: a "Booked" email for a booking cancelled in the meantime is skipped, and only "Cancelled" goes out.
- Locally, emails reach Mailpit only while `php artisan schedule:work` (or `php artisan queue:work`) runs.
- When CourtKu moves to paid hosting, a real worker (`php artisan queue:work`) replaces the tick's part with no code change.
