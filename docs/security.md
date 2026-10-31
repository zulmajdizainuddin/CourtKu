# Security checklist

What protects CourtKu, where it lives in the code, and what is still open. Review it before every milestone and before real money is involved (M6).

## Who can reach what

- [x] **Logins are cookies, not tokens.** The browser only talks to the web app, so Laravel's session cookie is first-party, `HttpOnly` (JavaScript can't read it), `SameSite=Lax`, and `Secure` on the live site (`SESSION_SECURE_COOKIE=true`). [ADR-004](adr/004-sanctum-cookies-one-origin.md)
- [x] **CSRF.** Every change (`POST`, `PUT`, `DELETE`) needs Sanctum's XSRF token, which only pages on CourtKu can read.
- [x] **Only the web app may say who the visitor is.** Forwarded IP and host headers are only believed with `WEB_PROXY_SECRET`; direct callers can't fake their IP to get around rate limits. `App\Http\Middleware\TrustWebProxy`, [ADR-025](adr/025-web-app-proves-itself-with-a-secret.md)
- [x] **Two-factor login for owners and the admin**, with no exceptions (`two-factor` middleware, 403 `TWO_FACTOR_REQUIRED`). Sensitive actions ask for the password again (423 `PASSWORD_CONFIRMATION_REQUIRED`).
- [x] **Verified email before booking** (`verified` middleware, FR-35).
- [x] **Venues can't see each other's data.** Every dashboard route checks a Policy (`can:...`) and loads child records through the venue (`scopeBindings`), so `/manage/venues/1/bookings/<a booking of venue 2>` is 404. `tests/Feature/TenantIsolationTest.php`
- [x] **Public pages show only public fields.** `PublicVenueResource` and `PlayerBookingResource` never include owner, staff or other players' details.

## Passwords and secrets

- [x] Passwords are stored as bcrypt hashes (Laravel default) and must be at least 8 characters (`Password::default()`).
- [x] Login: 5 tries a minute per email and IP; two-factor codes: 5 a minute (`FortifyServiceProvider`).
- [x] Secrets live only in `.env` (local) and Render/Vercel settings (live), never in git or chat. `.env.example` has empty values only.
- [x] CourtKu holds no venue's Stripe keys: venues connect through Stripe Connect. [ADR-024](adr/024-venues-connect-through-stripe-connect.md)
- [x] Shared secrets are compared with `hash_equals` (cron tick, web proxy, webhook signatures), so timing gives nothing away.
- [x] The web proxy secret is removed from the request before controllers, logs or error reports can see it.
- [x] `APP_DEBUG=false` on the live site, so errors never show code or settings.

## Requests from outside

- [x] **Stripe webhooks are signed.** `Stripe-Signature` (HMAC of time + body, 5 minutes tolerance) is checked before anything is read; bad ones get 401. The amount paid is checked against the booking. [ADR-006](adr/006-hosted-payment-page-webhook.md)
- [x] **Webhooks count once.** `webhook_events.event_key` is unique, and payments are processed with row locks.
- [x] **The cron tick needs `X-Cron-Secret`** (403 without it, 404 when no secret is set). [ADR-022](adr/022-timed-jobs-without-a-scheduler.md)
- [x] **Bookings can't be sent twice.** `Idempotency-Key` header, unique per user.
- [x] **No double bookings.** One row per court per half hour with a unique index. [ADR-005](adr/005-slot-rows-unique-index.md)

## Rate limits (per visitor IP, or per user when logged in)

| What | Limit |
| --- | --- |
| Public venue pages, times and prices | 60 a minute; the web app's own server has a separate 600 |
| Booking, cancelling, paying | 10 a minute |
| Player booking pages | 60 a minute |
| Connecting Stripe, staff invites, invite codes | 10 a minute |
| Photo uploads | 10 a minute, max 4 MB, JPEG/PNG/WebP only |
| Login / two-factor code | 5 a minute |
| Stripe webhook | 120 a minute |
| Cron tick | 10 a minute |

## Browser safety headers

- [x] **API** (`App\Http\Middleware\SecurityHeaders`): `X-Content-Type-Options: nosniff`, `Content-Security-Policy: default-src 'none'; frame-ancestors 'none'` (no framing), `Referrer-Policy: no-referrer`, and `Strict-Transport-Security` over HTTPS. `X-Frame-Options` comes from the Docker image's nginx, so Laravel doesn't send a second, different one.
- [x] **Web pages** (`apps/web/next.config.ts`): `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (no camera, microphone or location), and a CSP that blocks framing, plugins, outside `<base>` links and outside form targets. Vercel adds `Strict-Transport-Security`.
- [x] **No raw HTML from users.** React escapes everything it shows, and the web app has no `dangerouslySetInnerHTML`.
- [x] **No SQL built from user input.** All queries go through Eloquent or the query builder with bound values.
- [x] **Photos are re-processed** by Cloudinary on upload, which also strips location data (EXIF). [ADR-018](adr/018-photos-on-cloudinary.md)

## Knowing when something breaks

- [x] **Uptime alerts:** UptimeRobot checks the API's `/api/health` and the web home page every 5 minutes and emails when either is down.
- [x] **Error alerts:** Sentry emails about unexpected errors in the API and the web app (docs/deploy.md). It gets the error and the line of code only: no request bodies, IP addresses, cookies, headers, user details or code values (`config/sentry.php`, `apps/web/lib/sentry.ts`). Business rules saying no are never reported.
- [x] **Nightly backups**, locked with a password, kept in a private repository and restored into an empty database every night to prove they work ([ADR-029](adr/029-nightly-locked-backups-in-a-private-repo.md)). Logins (sessions) are never in a backup.
- [x] **After every deploy** the live site is checked automatically ([ADR-026](adr/026-no-staging-server-check-after-deploy.md)).

## Dependencies

- [x] CI runs `composer audit` and `npm audit --audit-level=high` on every pull request, so a package with a known hole blocks the merge.

## Still open

- [ ] **Scripts in the web CSP.** A full `script-src` needs a fresh nonce on every page, which turns off Next.js' static and cached pages. Revisit if we add anything that shows user-written HTML.
- [ ] **A staging copy** to try changes before the live site. Not on free plans ([ADR-026](adr/026-no-staging-server-check-after-deploy.md)); instead every deploy is checked automatically by the After deploy workflow.
- [ ] **Own domain and email sender**, so emails stop landing in spam and DMARC passes.
- [ ] **A general limit on dashboard routes.** Only the routes above are limited; the rest need a logged-in owner or staff member with two-factor login, but a per-user limit (e.g. 120 a minute) would still help.
- [ ] **Stronger password rule** (e.g. refuse passwords found in known leaks with `Password::uncompromised()`), before real customers.
