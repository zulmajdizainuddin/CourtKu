# Deploying CourtKu

How the live demo is hosted, and every setting it needs. Everything runs on free plans with no card.

```
Browser ──► courtku.vercel.app (Vercel, Singapore) ──► courtku-api.onrender.com (Render, Singapore) ──► MySQL (Aiven)
             Next.js web app                           Laravel API in Docker
```

| Part | Where | Deploys when |
| --- | --- | --- |
| Web app (`apps/web`) | Vercel Hobby, region `sin1` | Every push to `main` (pull requests get preview URLs) |
| API (`apps/api`) | Render free web service, region Singapore, built from `apps/api/Dockerfile` | Every push to `main` |
| MySQL 8 | Aiven free plan | Always on (powered off after long inactivity; switch on again in the Aiven console) |
| Email | Gmail SMTP with an app password | n/a |

Free plan limits to know:

- Render puts the API to sleep after 15 minutes without requests. The next request waits about a minute while it wakes up.
- There is no Redis yet: cache and sessions use MySQL. Redis returns with the background worker in a later milestone.
- Moving to an always-on server (e.g. Oracle Cloud with Coolify) only needs the same Docker image and these variables.

## API environment variables (set in Render)

Never commit real values. `APP_KEY` and passwords live only in Render.

| Variable | Value | Why |
| --- | --- | --- |
| `APP_NAME` | `CourtKu` | Shown in emails; also names the session cookie (`courtku-session`) |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | Never show error details to visitors |
| `APP_KEY` | output of `php artisan key:generate --show` | Encrypts cookies and secrets. Keep a copy somewhere safe |
| `APP_URL` | `https://courtku.vercel.app` | Links in emails point to the web app |
| `FRONTEND_URL` | `https://courtku.vercel.app` | Password reset links |
| `SANCTUM_STATEFUL_DOMAINS` | `courtku.vercel.app` (no `https://`) | Lets the web app log in with cookies |
| `SESSION_DRIVER` | `database` | |
| `SESSION_SECURE_COOKIE` | `true` | Cookie is only sent over HTTPS |
| `CACHE_STORE` | `database` | |
| `QUEUE_CONNECTION` | `sync` | No background worker yet |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | from the Aiven service's connection information | |
| `DB_DATABASE` | `courtku` | Created in Aiven under Databases |
| `MYSQL_ATTR_SSL_CA` | `/etc/secrets/aiven-ca.pem` | Aiven requires encrypted connections; this is its CA certificate |
| `MAIL_MAILER` | `smtp` (or `log` to write emails to the log instead of sending) | |
| `MAIL_HOST` / `MAIL_PORT` | `smtp.gmail.com` / `587` | |
| `MAIL_USERNAME` | the CourtKu Gmail address | |
| `MAIL_PASSWORD` | the 16-letter Gmail **app password** (not the normal password) | |
| `MAIL_FROM_ADDRESS` | the CourtKu Gmail address | Gmail rejects other "from" addresses |
| `MAIL_FROM_NAME` | `CourtKu` | |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stderr` / `warning` | Logs show in Render; use `debug` only while reading emails from the log |
| `PORT` | `8080` | The port nginx listens on inside the image |

Render settings:

- **Root Directory** `apps/api`, **Language** Docker, **Region** Singapore, **Instance type** Free.
- **Secret File** `aiven-ca.pem`: the CA certificate downloaded from the Aiven service overview.
- **Health Check Path** `/api/health`.

## Web environment variables (set in Vercel)

| Variable | Value |
| --- | --- |
| `API_URL` | `https://courtku-api.onrender.com` |

Vercel project settings: **Root Directory** `apps/web`, framework **Next.js**.

## Known gaps before real customers

- Laravel trusts `X-Forwarded-*` headers from any source ([ADR-004](adr/004-sanctum-cookies-one-origin.md)). The API must only accept traffic from the proxy before it handles real bookings.
- No queue worker, scheduler, backups or staging environment yet.
