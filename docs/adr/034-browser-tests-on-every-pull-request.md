# ADR-034: Browser tests on every pull request, with a pretend Stripe

- **Status:** Accepted
- **Date:** 2026-11-13

## Context

The plan (section 14) asks for Playwright end-to-end tests "against staging" after each merge: a player books and pays in the sandbox, staff add a walk-in, an owner changes a price. There is no staging server (ADR-026). The API tests (Pest) and web tests (Vitest) each check one side; nothing yet clicked through the real web app talking to the real API, where bugs like a page calling the wrong address, a missing proxy header or a broken layout on a phone show up.

Online payment goes through Stripe's own page (ADR-006). Driving Stripe's real page from CI would need a Stripe key in GitHub, the internet, and Stripe never changing its page.

## Decision

- **A fourth required check, "End-to-end"**, on every pull request and push to main (`.github/workflows/ci.yml`). It runs the real API (`php artisan serve`, port 8100) on its own MySQL database and Mailpit, and a production build of the web app (`next start`, port 3100), then Playwright clicks through 4 flows:
  1. A guest finds a venue on the home page, reads its refund policy and signs up (opening the verification link from the email in Mailpit).
  2. A player books, pays online and cancels.
  3. Staff add a walk-in for the next half hour and check it in.
  4. An owner logs in with two-factor, changes a court's price, and the booking page shows the new price.
- **Twice: on a laptop screen and on a 360 px phone** (Chromium), the plan's smallest screen (NFR-08). Each screen size has its own player, staff and owner, books a different day or court, so the two runs never clash; tests run one at a time.
- **A pretend Stripe** (`App\Payments\PretendGateway`, `PAYMENT_GATEWAY=pretend`): payment pages are kept in the cache, and CourtKu shows its own plain "Pretend Stripe" page at `/api/pretend-checkout/{id}` with a Pay link. Everything after that is the real code: the player comes back and CourtKu asks the gateway how the payment stands (FR-22). It is refused on the live site: `PretendGateway::enabled()` is false in production, so the gateway falls back to Stripe and the page is 404.
- **Its own data**: `E2eSeeder` (refused in production) makes one venue open 24 hours with three priced courts and a ready pretend payment account, and the accounts. `apps/web/e2e/start-api.mjs` writes `apps/api/.env.e2e` from `.env.e2e.example`, checks the API really points at an `*_e2e` database before emptying it, loads the data and starts the API.
- Locally: `npm run e2e` in `apps/web` (Docker's MySQL and Mailpit running; `E2E_PHP` set to Herd's PHP on Windows).

## Alternatives considered

- **Real Stripe test mode in CI:** closest to real life, but needs a key in GitHub secrets, depends on Stripe being up and on its page staying the same, and is slow. Stripe itself stays covered by `StripeGatewayTest` (recorded answers) and by trying the live demo.
- **After merging only, against the live site:** the plan's idea with staging, but a broken flow would already be on main and live, and tests would leave bookings in the live demo.
- **Three browsers (Chromium, Firefox, WebKit):** more coverage of browser differences, but three times slower; the phone-size run catches the layout problems that matter most.

## Consequences

- A pull request that breaks one of the 4 flows can't be merged. Each pull request takes about 5 to 8 minutes longer to check.
- The pretend gateway is code that ships in the image but never runs on the live site; its two tests prove it stays off there.
- When a test fails in CI, the run keeps a report with screenshots and a trace for 7 days (Actions → the run → Artifacts).
