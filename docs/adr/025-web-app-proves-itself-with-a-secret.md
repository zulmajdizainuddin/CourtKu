# ADR-025: The web app proves itself to the API with a shared secret

- **Status:** Accepted
- **Date:** 2026-11-06
- **Changes:** [ADR-004](004-sanctum-cookies-one-origin.md): Laravel no longer trusts `X-Forwarded-*` headers from anyone.

## Context

Browsers reach the API through the web app (ADR-004): Vercel forwards `/api/*`, `/auth/*` and `/sanctum/csrf-cookie` to Render, and Render's own proxy passes them to Laravel. Laravel needs two facts from the forwarded headers:

- the visitor's IP, for rate limits (login, booking, public pages);
- the host the visitor used (`courtku.vercel.app`), for links it builds.

Until now Laravel trusted these headers from every caller (`trustProxies(at: '*')`). The API is also reachable directly at `courtku-api.onrender.com`, so anyone could send a made-up `X-Forwarded-For` with every request and never hit a rate limit, or a made-up `X-Forwarded-Host` so a verification email pointed to another site.

Trusting only the proxies' IP addresses doesn't work here: Vercel and Render use many addresses that change, and both are shared with every other customer.

## Decision

The web app and the API share one secret, `WEB_PROXY_SECRET`, set in both Vercel and Render.

- The web app's `proxy.ts` adds the secret and the visitor's IP (`X-Web-Client-Ip`, taken from the `X-Forwarded-For` that Vercel sets and visitors can't change) to every request it forwards. The web app's own server sends the secret without an IP when it fetches a public venue page for itself.
- `App\Http\Middleware\TrustWebProxy` replaces Laravel's TrustProxies:
  - **right secret and an IP**: a visitor through the web app. The API believes the IP and host it reports (`via` = `browser`);
  - **right secret, no IP**: the web app's server (`via` = `web-server`). Public reads give it a bucket of its own, 600 a minute, instead of sharing one visitor's 60;
  - **anything else** (Stripe, the cron service, anyone calling directly): `via` = `direct`. Only Render's own proxy (a private address) is believed, and only for the caller's IP and http/https; forwarded host headers are dropped.
- The secret is removed from the request before anything else sees it, so it never shows up in logs or error reports.
- With no secret set, the API behaves as before. Nothing breaks while the secret is being added on both sides.
- `GET /api/health` reports `via`, so after a deploy anyone can check that the web app is recognised: through `courtku.vercel.app/api/health` it says `browser`.

## Alternatives considered

- **Allow-list of Vercel's IP addresses:** Vercel doesn't publish a fixed list on the free plan, and the addresses are shared with every Vercel customer.
- **Keep trusting everyone:** rate limits would mean nothing to anyone who reads the docs.
- **Sign each request (HMAC of the body and time):** stronger against a leaked secret being replayed, but the proxy can't read request bodies without slowing every upload, and the secret only travels over HTTPS between two servers.

## Consequences

- Rate limits now really count per visitor, and direct callers can't fake who they are.
- One more secret to keep the same in two places. If they differ, browser traffic is treated as direct: rate limits then count per Vercel address and links may use the API's host, so the health check is the first thing to look at after changing it.
- A leaked secret lets someone fake their IP again (nothing more), until it is changed in both places.
