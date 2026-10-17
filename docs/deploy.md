# Deploying CourtKu

How the live demo is hosted, and every setting it needs. Everything runs on free plans.

```
Browser ──► courtku.vercel.app (Vercel, Singapore) ──► API on Oracle Cloud (Singapore)
             Next.js web app                           Coolify: Laravel API + MySQL + Redis
```

| Part | Where | Deploys when |
| --- | --- | --- |
| Web app (`apps/web`) | Vercel Hobby, region `sin1` | Every push to `main` (pull requests get preview URLs) |
| API (`apps/api`) | Oracle Cloud Always Free ARM VM running Coolify | Every push to `main`, built from `apps/api/Dockerfile` |
| MySQL 8 and Redis | Same VM, managed by Coolify | Always on |
| Email | Gmail SMTP with an app password | n/a |

## API environment variables (set in Coolify)

Never commit real values. `APP_KEY` and the passwords live only in Coolify.

| Variable | Value | Why |
| --- | --- | --- |
| `APP_NAME` | `CourtKu` | Shown in emails; also names the session cookie (`courtku-session`) |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | Never show error details to visitors |
| `APP_KEY` | output of `php artisan key:generate --show` | Encrypts cookies and secrets. Keep a copy somewhere safe, outside backups |
| `APP_URL` | `https://<web host>` | Links in emails point to the web app |
| `FRONTEND_URL` | `https://<web host>` | Password reset links |
| `SANCTUM_STATEFUL_DOMAINS` | `<web host>` (no `https://`) | Lets the web app log in with cookies |
| `SESSION_DOMAIN` | empty | Cookie stays on the web host |
| `SESSION_SECURE_COOKIE` | `true` | Cookie is only sent over HTTPS |
| `SESSION_DRIVER` | `database` | |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | from the Coolify MySQL resource | |
| `CACHE_STORE` | `redis` | |
| `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` | from the Coolify Redis resource | |
| `QUEUE_CONNECTION` | `sync` | No background worker yet; becomes `redis` + Horizon in M5 |
| `MAIL_MAILER` | `smtp` | |
| `MAIL_HOST` / `MAIL_PORT` | `smtp.gmail.com` / `587` | |
| `MAIL_USERNAME` | the Gmail address | |
| `MAIL_PASSWORD` | the 16-letter Gmail **app password** (not the normal password) | |
| `MAIL_FROM_ADDRESS` | the Gmail address | Gmail rejects other "from" addresses |
| `MAIL_FROM_NAME` | `CourtKu` | |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stderr` / `warning` | Logs show in Coolify; no personal data in logs |

Health check path for Coolify: `/api/health` on port `8080`.

## Web environment variables (set in Vercel)

| Variable | Value |
| --- | --- |
| `API_URL` | `https://<api host>` (the Coolify URL of the API) |

Vercel project settings: **Root Directory** `apps/web`, framework **Next.js**.

## Known gaps before real customers (M5)

- Laravel trusts `X-Forwarded-*` headers from any source ([ADR-004](adr/004-sanctum-cookies-one-origin.md)). The API must only accept traffic from the proxy before it handles real bookings.
- No queue worker, scheduler or backups yet.
- Staging environment is added in M5.
