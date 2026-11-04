# Introduction

The API behind CourtKu: venues, courts, prices, live availability, bookings and online payments for badminton, futsal and pickleball courts in Malaysia.

<aside>
    <strong>Base URL</strong>: <code>https://courtku.vercel.app</code>
</aside>

    This is the API the CourtKu web app uses. It is a REST API under `/api/v1` that answers in JSON (ADR-008).

    <aside>The live API runs on a free plan that sleeps when nobody uses it: the first request after a quiet period can take up to a minute.</aside>

    **How the web app calls it.** The browser only ever talks to the web app (https://courtku.vercel.app), which forwards `/api`, `/auth` and `/sanctum` to this API. Logins are a normal first-party session cookie from Laravel Sanctum, not tokens (ADR-004), so the examples show the paths as the web app forwards them.

    **Logging in.** `GET /sanctum/csrf-cookie` once, then `POST /auth/login` with the `X-XSRF-TOKEN` header. Every request that changes something needs that header. Venue owners and the admin must also pass two-factor login (`POST /auth/two-factor-challenge`).

    **Errors** always look the same: `{ "error": { "code": "SLOT_TAKEN", "message": "...", "details": { ... } } }`. A form error is `422 VALIDATION_FAILED` with `details.fields`; a business rule saying no is `409` with its own code.

    **Times** are sent and returned with the Malaysia offset, e.g. `2026-11-06T20:00:00+08:00`. Money is in sen (`2000` = RM 20.00), with a ready-made `"RM 20.00"` text next to it.

    The source and the decisions behind it: https://github.com/zulmajdizainuddin/CourtKu

