# ADR-030: The live demo runs on a rolling demo world, kept fresh by the minute tick

- **Status:** Accepted
- **Date:** 2026-11-10
- **Changes:** [ADR-024](024-venues-connect-through-stripe-connect.md): two venues may share one Stripe account (the demo venue and CourtKu's own Test Venue).

## Context

M5 is done when the live demo runs on demo data: labelled, fictional venues with 1,000+ bookings made through the real booking engine, under `@example.com` addresses, reset every night (plan section 15).

Visitors, often recruiters, should see a CourtKu that looks in use: busy evenings, quiet mornings, cancellations, no-shows, money in the reports. Every booking must obey the same rules as a real one, or the demo shows something the app would never allow.

The live site runs on free plans: Render's small shared CPU and an Aiven database across the network. Making about 10,000 bookings through the engine takes minutes on a laptop and much longer on Render. A full "wipe and rebuild every night" would either run for hours or need a big machine.

## Decision

- **20 fictional venues** across Malaysia (`App\Demo\DemoVenues`): every name ends in "(Demo)", `venues.is_demo` is set, and each venue page says it is made up and that what visitors book there is cleared overnight. **40 fictional players** use `@demo.example.com` addresses.
- **A rolling window instead of a nightly wipe** (`App\Demo\DemoWorld`): the minute tick spends about 8 seconds keeping the demo world right, nearest days first. A day is filled court by court and can be spread over several ticks (`demo_days.courts_done`): on the live site one big day takes longer than a whole tick, and cron-job.org gives up on a call after 30 seconds:
  1. the venues and players exist;
  2. days older than 30 days are removed;
  3. ended games are finished: about 1 in 20 a no-show, the rest checked in, completed and paid;
  4. between 3 and 4 am, what visitors booked at demo venues is removed;
  5. every day from 30 days ago to 13 days ahead has its bookings, made through `BookingService` (so opening hours, prices, slot lengths and double-booking rules all apply). Past days are booked "the day before" by moving the clock back while making them. Each day is filled once (`demo_days`), with the same bookings whether in one go or over several ticks.
- Finishing ended games is done with plain updates, not the state machine: it would only add an audit entry per booking.
- **Nobody is emailed:** demo players and demo walk-ins never get booking emails or reminders, and on the live site nothing is ever sent to `example.com` or its subdomains (the public demo account included), so the demo can't use up Brevo's daily allowance.
- **One demo venue takes online payment** with the test card, through the Stripe test account CourtKu's own Test Venue connected (`DEMO_STRIPE_ACCOUNT_ID`). So an account may belong to more than one venue now; nothing looks a venue up by its account, and owners still connect through Stripe's own sign-up. Past "paid online" demo bookings get payment rows with gateway `demo`; nothing is sent to Stripe for them.
- A failure in the demo world is reported to Sentry and never stops the tick's real jobs.
- It is off unless `DEMO_WORLD=true`. `php artisan courtku:demo --until-done` fills everything at once (on a laptop).

## Alternatives considered

- **Wipe and rebuild every night** (from GitHub Actions or the tick): matches the plan's wording, but too slow on free plans and the demo would be half-empty for hours each night.
- **Insert rows directly, without the engine:** much faster, but the demo could show bookings the app would refuse.
- **A handful of hand-made bookings:** quick, but reports and schedules would look empty.

## Consequences

- After a deploy with `DEMO_WORLD=true` the demo fills itself: today and the next days within minutes, the full history within a few hours.
- About 10,000 demo bookings exist at any time, a few tens of MB in the database. The admin totals leave demo venues out.
- What visitors book at a demo venue disappears overnight, which the venue page says.
- New booking rules apply to demo bookings automatically, because they go through the engine.
