# CourtKu

Book a badminton, futsal or pickleball court in under two minutes. CourtKu is a multi-tenant SaaS: each venue gets its own booking page, players see live availability and pay online, and owners run their day from a dashboard.

> **Status:** M4 Payments is live. Venue owners sign up (with two-factor login), wait for approval, set up their venue, prices and booking rules, and connect their own Stripe account (Stripe Connect). Players book at `/v/<venue>/book` and pay online on Stripe's page while the court is held for them, or pay at the counter. Staff run the day from a schedule (walk-ins, blocks, check-in, paid at the counter), and owners record refunds. Notifications, reports and production hardening come next (M5).

## Live demo

**https://courtku.vercel.app**

Log in with the demo account `player@example.com` / `CourtKu-Demo-2026`, or register your own.

Online payments run in **Stripe test mode: no real money is charged.** Pay with the test card `4242 4242 4242 4242`, any future expiry date and any CVC (`4000 0000 0000 0002` is always declined).

The API runs on a free plan that sleeps when nobody uses it, so the first request after a quiet period can take up to a minute.

## Tech stack

| Part | Technology |
| --- | --- |
| API | Laravel 13 (PHP 8.4), Sanctum + Fortify for auth, Pest for tests, Larastan + Pint for code quality |
| Web | Next.js 16 (TypeScript, App Router), Tailwind CSS, shadcn/ui, TanStack Query, React Hook Form + Zod, Vitest |
| Data | MySQL 8.4, Valkey (Redis-compatible) for cache and queues |
| CI | GitHub Actions: lint, static analysis, tests, build and the production Docker image on every pull request |
| Hosting | Vercel (web), Render (API, Docker), Aiven (MySQL), Brevo (email), UptimeRobot (monitoring) |

## How it fits together

```
Browser ──► Next.js web app (localhost:3000) ──► Laravel API (localhost:8000) ──► MySQL / Redis
             pages + forwards /api, /auth,         business rules, auth,
             /sanctum to the API                   bookings, payments
```

The browser only ever talks to the web app. The web app forwards API calls to Laravel, so the login cookie is a normal first-party cookie and there is no CORS setup ([ADR-004](docs/adr/004-sanctum-cookies-one-origin.md)).

## Repository layout

```
apps/api/     Laravel API
apps/web/     Next.js web app
docs/adr/     Architecture decision records: why things are built this way
docker/       Config for the local Docker services
docker-compose.yml   MySQL, Redis and Mailpit for local development
.github/workflows/   CI
```

## Run it locally (Windows)

You need: **PHP 8.4** (Laravel Herd), **Composer**, **Node.js 24**, **Git** and **Docker Desktop**. See [ADR-011](docs/adr/011-windows-native-local-dev.md) for why the apps run directly on Windows and only the databases run in Docker.

1. Start the databases and the email catcher:

   ```bash
   docker compose up -d
   ```

2. Set up and start the API (in a terminal, from `apps/api`):

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   ```

3. Start the web app (in a second terminal, from `apps/web`):

   ```bash
   npm install
   npm run dev
   ```

4. Open http://localhost:3000. Emails (verification, password reset) appear in Mailpit at http://localhost:8025.

**Local demo data** (created by `--seed`): the account `player@example.com` / `CourtKu-Demo-2026`, and a fictional approved venue at http://localhost:3000/v/arena-demo

**Photo uploads** need a free Cloudinary account; see the `CLOUDINARY_*` lines in `apps/api/.env.example` ([ADR-018](docs/adr/018-photos-on-cloudinary.md)).

**Online payments** need a free Stripe account in test mode with **Connect** turned on; put its secret key (`sk_test_...`) in `STRIPE_SECRET_KEY` ([ADR-023](docs/adr/023-stripe-test-mode-instead-of-billplz.md), [ADR-024](docs/adr/024-venues-connect-through-stripe-connect.md)). Stripe can't reach your laptop, so locally a payment is confirmed when the player comes back from Stripe's page. Run `php artisan schedule:work` for the every-minute jobs (hold expiry, payment checks, and sending the queued booking emails to Mailpit, [ADR-027](docs/adr/027-emails-queued-and-sent-by-the-tick.md)). Everything else works without these.

## Tests and checks

| What | Command | Where |
| --- | --- | --- |
| API tests | `vendor/bin/pest` | `apps/api` |
| API static analysis | `vendor/bin/phpstan analyse` | `apps/api` |
| API code style (fix) | `vendor/bin/pint` | `apps/api` |
| Web tests | `npm test` | `apps/web` |
| Web lint and types | `npm run lint` and `npm run typecheck` | `apps/web` |

The same checks run in GitHub Actions on every pull request.

## Deployment

How the live demo is hosted and every production setting: [docs/deploy.md](docs/deploy.md).

## Decisions

Every important design decision has a short write-up in [docs/adr](docs/adr/README.md). The security checklist is in [docs/security.md](docs/security.md).
