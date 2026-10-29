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
| MySQL 8 | Aiven free plan | Always on, except when Aiven powers it off (see below) |
| Email | Brevo free plan (300 emails/day), SMTP on port 2525 | n/a |
| Venue photos | Cloudinary free plan (25 credits/month), [ADR-018](adr/018-photos-on-cloudinary.md) | n/a |
| Uptime | UptimeRobot, every 5 minutes on `/api/health` and the web home page | n/a |

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
| Every request times out, even `/up` | Render instance stuck or failed to deploy | Render → **Events** and **Logs**; **Manual Deploy → Restart service** |

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
| `QUEUE_CONNECTION` | `sync` | No background worker yet |
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

## Web environment variables (set in Vercel)

| Variable | Value |
| --- | --- |
| `API_URL` | `https://courtku-api.onrender.com` |

Vercel project settings: **Root Directory** `apps/web`, framework **Next.js**.

## Known gaps before real customers

- Laravel trusts `X-Forwarded-*` headers from any source ([ADR-004](adr/004-sanctum-cookies-one-origin.md)). The API must only accept traffic from the proxy before it handles real bookings.
- No queue worker, scheduler, backups or staging environment yet.
