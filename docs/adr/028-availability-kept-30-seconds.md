# ADR-028: The public time grid is kept for 30 seconds and thrown away on every change

- **Status:** Accepted
- **Date:** 2026-11-08

## Context

Every visitor on a venue's booking page asks for the day's time grid (`GET /api/v1/venues/{slug}/availability`): which half hours are free on which court, at what price. Working it out takes several queries (opening hours, closures, courts, price rules, bookings, blocks) and some arithmetic for every half hour. On the live set-up the database is a free Aiven server in another data centre, so each query costs a round trip. A popular evening, with many people looking at the same day, would ask the same question again and again.

The plan (M2, step 4) asked for a 30-second cache. A plain 30-second cache has a cost: a slot booked a moment ago would still look free for up to 30 seconds, and an owner's new price wouldn't show at once.

## Decision

- The grid for one venue, day and sport is kept for 30 seconds (`App\Services\AvailabilityCache`), in Laravel's cache (MySQL on the live site, Redis locally).
- Each venue has a **version**, a random word kept in the cache. Every saved or deleted booking, block, opening period, closure, court, price rule or venue setting gives the venue a new version (`App\Models\Concerns\ClearsAvailabilityCache`). The few changes made with plain queries (saving the whole week of opening hours, reordering courts) ask for a new version themselves.
- A kept grid is used only while its version still matches, so every change shows at once. The new version is set only after the change is saved (`DB::afterCommit`), and the version is read before a grid is worked out, so a grid worked out while a change was being saved is never used again.
- One cache entry per venue, day and sport, overwritten each time, so old copies don't pile up in the database.
- Booking itself never uses the kept grid. A booking is checked against the database every time, and the unique index on half hours stops double booking (ADR-005), so a kept grid can at worst show a time that has just started or just come within the venue's notice, and the booking is then refused with a clear message.

## Alternatives considered

- **A plain 30-second cache:** simplest, but bookings and price changes would show up to 30 seconds late.
- **No cache:** always right, but every visitor costs a full set of queries.
- **Forget each key on change:** a change doesn't know which days and sports were kept, and the cache can't list its keys on every store.

## Consequences

- Repeat visits to the same day are much cheaper (locally about 100 ms down to about 30 ms).
- A change made directly in the database, outside CourtKu, shows within 30 seconds.
- A new model or query that changes free times must clear the venue's version too; `tests/Feature/Venues/AvailabilityCacheTest.php` lists every change that must show at once.
