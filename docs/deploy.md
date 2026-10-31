# Deploying CourtKu

How the live demo is hosted, and every setting it needs. Everything runs on free plans with no card.

```
Browser ──► courtku.vercel.app (Vercel, Singapore) ──► courtku-api.onrender.com (Render, Singapore) ──► MySQL (Aiven)
             Next.js web app                           Laravel API in Docker
```

| Part | Where | Deploys when |
| --- | --- | --- |
| Web app (`apps/web`) | Vercel Hobby, region `sin1` | Every push to `main` (pull requests get preview URLs, which use the **live** API, see below) |
| API (`apps/api`) | Render free web service, region Singapore, built from `apps/api/Dockerfile` | Every push to `main` |
| MySQL 8 | Aiven free plan | Always on, except when Aiven powers it off (see below) |
| Email | Brevo free plan (300 emails/day), SMTP on port 2525 | n/a |
| Venue photos | Cloudinary free plan (25 credits/month), [ADR-018](adr/018-photos-on-cloudinary.md) | n/a |
| Uptime | UptimeRobot, every 5 minutes on `/api/health` and the web home page | n/a |
| Timed jobs | cron-job.org, every minute on `POST /api/tick` ([ADR-022](adr/022-timed-jobs-without-a-scheduler.md)) | n/a |
| After-deploy check | GitHub Actions, after every merge to `main` ([ADR-026](adr/026-no-staging-server-check-after-deploy.md)) | n/a |

Free plan limits to know:

- Render puts the API to sleep after 15 minutes without requests. The UptimeRobot check every 5 minutes keeps it awake; Render's 750 free hours a month cover one service running all month.
- Render's free plan blocks outgoing email on ports 25, 465 and 587. Brevo's port 2525 is not blocked.
- Aiven powers off free databases it considers inactive (health checks don't count as activity) and emails a warning first. The API keeps running without it: pages that need the database fail and `/api/health` returns 503, so UptimeRobot alerts.
- There is no Redis yet: cache and sessions use MySQL. Redis returns with the background worker in a later milestone.
- Moving to an always-on server (e.g. Oracle Cloud with Coolify) only needs the same Docker image and these variables.

## When something is down

| Symptom | Likely cause | Fix |
| --- | --- | --- |
| UptimeRobot says down, `/api/health` shows `"database": false` | Aiven powered the database off | Aiven console → `courtku-db` → **Power on**. Wait for **Running**; the API reconnects by itself. If tables were never created, Render → **Manual Deploy → Restart service** runs the migrations again |
| Emails stop arriving, Brevo logs are empty | Brevo SMTP key expired (1-year expiry, or 90 days without sending) | Brevo → **SMTP & API** → generate a new key → update `MAIL_PASSWORD` in Render |
| Booking emails stop, but verify-email and password emails still arrive | The minute tick isn't running, so queued emails wait in `jobs` | cron-job.org → the CourtKu tick's history. Once it runs again, the waiting emails all go out. Emails that failed 3 times are listed by `php artisan queue:failed` (from the laptop, as in *Make an account the platform admin*); `php artisan queue:retry all` sends them again |
| Every request times out, even `/up` | Render instance stuck or failed to deploy | Render → **Events** and **Logs**; **Manual Deploy → Restart service** |
| The **After deploy** workflow fails with "still runs ... after 25 minutes" | The new code didn't deploy (build failed, or the deploy is still queued) | Render → **Events**, or Vercel → **Deployments**: read the failed build's log. Then re-run the workflow (Actions → After deploy → **Re-run jobs**) |
| The **After deploy** workflow fails a check after the deploy | The new code broke something on the live site | Fix forward with a new PR, or go back: Render → **Events** → the previous deploy → **Rollback**; Vercel → **Deployments** → the previous production deployment → **Instant Rollback** |

## After every deploy

There is no staging server ([ADR-026](adr/026-no-staging-server-check-after-deploy.md)): Render's free hours don't stretch to a second API, so every merge to `main` goes straight to the live site. To catch a bad deploy quickly, the **After deploy** workflow (`.github/workflows/after-deploy.yml`) runs after every merge:

1. It looks at which folders changed. For `apps/api` it waits until `https://courtku-api.onrender.com/api/health` reports the merged commit as `version`; for `apps/web`, until `https://courtku.vercel.app/version` does. Up to 25 minutes each.
2. Then it checks the live site: API and database healthy, the web app's secret accepted (`via` = `browser`), home and login pages, the login cookie, the not-found page and the safety headers.

GitHub emails whoever merged if it fails; see *When something is down* above. Run the same checks by hand from the repo root with `node .github/scripts/after-deploy-check.mjs`.

**Pull request previews** use the live API and the live database. They are only for looking at screens: never book, pay or change settings in a preview.

## Make an account the platform admin

The admin approves and suspends venues at `/admin`. No web page can make someone admin; only `php artisan courtku:make-admin`. Render's free plan has no shell, so run it from your own computer against the Aiven database:

1. Register the account on the live site and verify its email.
2. Save Aiven's CA certificate (the same text as Render's `aiven-ca.pem`). In the Aiven console, open the service → **Overview** → **Connection information**, click **Show** next to **CA certificate** and copy all of it, from `-----BEGIN CERTIFICATE-----` to `-----END CERTIFICATE-----`. Then in PowerShell:

   ```powershell
   Get-Clipboard | Set-Content -Encoding ascii "C:\path\to\aiven-ca.pem"
   Get-Content "C:\path\to\aiven-ca.pem" -TotalCount 1   # should print -----BEGIN CERTIFICATE-----
   ```

   (The **Download** button can save the file somewhere unexpected; copying avoids that.)
3. In PowerShell, from `apps/api`, set the database variables for this window only, then run the command. Variables set like this take priority over your local `.env`:

   ```powershell
   $env:DB_HOST = "<Aiven host>"; $env:DB_PORT = "<Aiven port>"; $env:DB_DATABASE = "courtku"
   $env:DB_USERNAME = "<Aiven user>"; $env:DB_PASSWORD = "<Aiven password>"
   $env:MYSQL_ATTR_SSL_CA = "C:\path\to\aiven-ca.pem"
   php artisan courtku:make-admin you@example.com
   ```

4. Close the PowerShell window afterwards, so later commands use your local database again.
5. Log in on the live site (refresh if you were already logged in). The admin must turn on two-factor login at **My account → Two-factor login** before `/admin` opens.

## Someone lost their phone and all their recovery codes

Owners and the admin must use two-factor login. Someone who lost both their phone and every recovery code cannot log in, by design. `php artisan courtku:reset-two-factor` turns two-factor off for that one account so they can set it up again on a new phone. It works for your own admin account too.

1. **Check it really is them.** A thief with a stolen password can also ask for this. Reply to the email address on their CourtKu account and wait for their answer from that address; never act on a message from a different address or a phone call alone.
2. Open PowerShell and set the database variables exactly as in steps 2 and 3 of the admin section above, then run:

   ```powershell
   php artisan courtku:reset-two-factor owner@example.com
   ```

3. It asks whether you checked who is asking. Answer `yes` only after step 1.
4. Close the PowerShell window. Tell them to log in with their password: their dashboard asks them to turn on two-factor login again, with new recovery codes.

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
| `QUEUE_CONNECTION` | `database` | Booking emails wait in the `jobs` table and the minute tick sends them ([ADR-027](adr/027-emails-queued-and-sent-by-the-tick.md)). `sync` sends them inside the request instead (slower booking, and lost if Brevo is down) |
| `WEB_PROXY_SECRET` | a long random text, the **same** value as in Vercel, see *Web app secret* below | Only requests carrying it may say who the visitor is ([ADR-025](adr/025-web-app-proves-itself-with-a-secret.md)). Empty = forwarded headers are believed from anyone |
| `CRON_SECRET` | a long random text, e.g. from a password manager's generator | The cron service sends it to `POST /api/tick` ([ADR-022](adr/022-timed-jobs-without-a-scheduler.md)). Empty = the endpoint is off |
| `STRIPE_SECRET_KEY` | Stripe (Sandbox) → **Developers → API keys → Secret key** (`sk_test_...`) | CourtKu's own platform key, with **Connect** turned on. Venues connect their own Stripe accounts through Stripe Connect and never give CourtKu a key ([ADR-024](adr/024-venues-connect-through-stripe-connect.md)). Test keys only |
| `STRIPE_WEBHOOK_SECRET` | the Connect webhook's **Signing secret** (`whsec_...`), see *Payment webhook* below | Proves each payment message really comes from Stripe ([ADR-006](adr/006-hosted-payment-page-webhook.md)). Without it every webhook is refused with 401 |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | from the Aiven service's connection information | |
| `DB_DATABASE` | `courtku` | Created in Aiven under Databases |
| `MYSQL_ATTR_SSL_CA` | `/etc/secrets/aiven-ca.pem` | Aiven requires encrypted connections; this is its CA certificate |
| `DB_CONNECT_TIMEOUT` | `3` (default, optional) | Seconds to wait for the database before giving up |
| `MAIL_MAILER` | `smtp` (or `log` to write emails to the log instead of sending) | |
| `MAIL_HOST` / `MAIL_PORT` | `smtp-relay.brevo.com` / `2525` | Render's free plan blocks 25, 465 and 587 |
| `MAIL_USERNAME` | the SMTP login from Brevo → **SMTP & API** (ends in `@smtp-brevo.com`) | |
| `MAIL_PASSWORD` | a Brevo **SMTP key** (not the account password) | Expires after 1 year, or after 90 days without sending |
| `MAIL_FROM_ADDRESS` | the CourtKu Gmail address, verified as a sender in Brevo | A free-mail sender may land in spam; a domain of our own fixes that later |
| `MAIL_FROM_NAME` | `CourtKu` | |
| `CLOUDINARY_CLOUD_NAME` | from Cloudinary → **Settings → API Keys** | Venue photos ([ADR-018](adr/018-photos-on-cloudinary.md)) |
| `CLOUDINARY_API_KEY` | from Cloudinary → **Settings → API Keys** | |
| `CLOUDINARY_API_SECRET` | from Cloudinary → **Settings → API Keys** (click the eye to show it) | Signs every upload; never sent to the browser |
| `CLOUDINARY_FOLDER` | `courtku` | Keeps live photos apart from local test photos (`courtku-local`) |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stderr` / `warning` | Logs show in Render; use `debug` only while reading emails from the log |
| `PORT` | `8080` | The port nginx listens on inside the image |

Render settings:

- **Root Directory** `apps/api`, **Language** Docker, **Region** Singapore, **Instance type** Free.
- **Secret File** `aiven-ca.pem`: the CA certificate downloaded from the Aiven service overview.
- **Health Check Path** `/up`. It only checks that the app runs, so a database outage doesn't make Render restart a working API. The full check, `/api/health`, is for UptimeRobot.

On every start, the container runs the database migrations (`apps/api/docker/45-courtku-migrate.sh`). If the database doesn't answer within 60 seconds, it skips them and starts the API anyway.

## Timed jobs (every minute)

Unpaid holds expire and finished bookings complete through `POST /api/tick` ([ADR-022](adr/022-timed-jobs-without-a-scheduler.md)). Render's free plan has no scheduler, so a free cron service calls it:

1. Make a long random secret (e.g. 40 letters and digits from a password manager) and set it as `CRON_SECRET` in Render. Render redeploys. The tick also sends the queued booking emails ([ADR-027](adr/027-emails-queued-and-sent-by-the-tick.md)).
2. Sign up at [cron-job.org](https://cron-job.org) (free) and create a cron job:
   - **URL** `https://courtku-api.onrender.com/api/tick`
   - **Schedule** every 1 minute
   - **Advanced → Request method** `POST`
   - **Advanced → Headers** `X-Cron-Secret` = the same secret
3. Save, then open the job's history: each run should answer **200** with `{"data":{"expired_holds":0,"completed_bookings":0,...,"emails_sent":0}}`. A 403 means the header doesn't match `CRON_SECRET`; a 404 means `CRON_SECRET` isn't set in Render.

Locally, run `php artisan schedule:work` in a terminal (or `php artisan courtku:tick` once). Players never wait on this job: whatever reads free times expires overdue holds first. Booking emails reach Mailpit only while it runs.

## Payment webhook (Stripe Connect)

Stripe tells CourtKu when a player has paid by calling `POST /api/v1/webhooks/payments/stripe`. One webhook covers every venue's connected account ([ADR-024](adr/024-venues-connect-through-stripe-connect.md)). Set it up once, in the Stripe dashboard (Sandbox):

1. **Developers → Webhooks → Add destination** (or **Add endpoint**).
2. **Events from:** **Connected accounts** (not "Your account").
3. **Events:** `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, `checkout.session.expired`.
4. **Endpoint URL:** `https://courtku-api.onrender.com/api/v1/webhooks/payments/stripe`
5. Save, open the new destination and reveal its **Signing secret** (`whsec_...`). Put it in Render as `STRIPE_WEBHOOK_SECRET`. Render redeploys.
6. After a test payment, the destination's event list should show **200** responses. A **401** means the signing secret in Render doesn't match.

Stripe can't reach a laptop, so locally payments are confirmed by CourtKu asking Stripe when the player returns (M4 PR 5).

## Web environment variables (set in Vercel)

| Variable | Value |
| --- | --- |
| `API_URL` | `https://courtku-api.onrender.com` |
| `WEB_PROXY_SECRET` | the **same** value as in Render ([ADR-025](adr/025-web-app-proves-itself-with-a-secret.md)). Not `NEXT_PUBLIC_`, so it never reaches browsers |

Vercel project settings: **Root Directory** `apps/web`, framework **Next.js**.

## Web app secret

The web app sends `WEB_PROXY_SECRET` with every request it forwards to the API, so the API knows it may believe the visitor's IP and host ([ADR-025](adr/025-web-app-proves-itself-with-a-secret.md)). To set it up or change it:

1. Make a long random secret (e.g. 40 letters and digits from a password manager).
2. Vercel → the project → **Settings → Environment Variables**: add `WEB_PROXY_SECRET` for **Production** (and **Preview**). Then **Deployments → Redeploy** the latest production deployment, because Vercel only reads variables when it builds.
3. Render → the API → **Environment**: add `WEB_PROXY_SECRET` with the same value. Render redeploys.
4. Check: open `https://courtku.vercel.app/api/health`. It should end with `"via":"browser"`. Opening `https://courtku-api.onrender.com/api/health` directly should say `"via":"direct"`. If the first one says `direct`, the two values differ.

Do Vercel before Render. While only Vercel has the secret, the API still believes forwarded headers like before. The other way round, the API would treat every visitor as a direct caller until the web app is redeployed.

## Known gaps before real customers

- No queue worker or backups yet. Timed jobs and booking emails depend on cron-job.org calling `/api/tick` ([ADR-027](adr/027-emails-queued-and-sent-by-the-tick.md)).
- No staging server ([ADR-026](adr/026-no-staging-server-check-after-deploy.md)): each merge goes straight to the live site, checked afterwards by the After deploy workflow.
