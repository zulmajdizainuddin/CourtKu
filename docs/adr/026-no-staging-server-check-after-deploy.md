# ADR-026: No staging server; every deploy is checked automatically instead

- **Status:** Accepted
- **Date:** 2026-11-06

## Context

The plan (M5) asks for separate staging and production: a second copy of the site where each change runs first, and production only updated after a look at staging.

Everything runs on free plans with no card:

- **Render** gives 750 free instance hours a month for all free services in a workspace together. The live API runs all month (cron-job.org calls it every minute), which is about 744 hours. A second, staging API would run the hours out, and then Render switches off every free service, the live API included, until the next month.
- **Aiven** allows one free MySQL service per account.
- The free container hosts we looked at either need a card or run far from Malaysia.

A staging API that is switched off except while testing would fit, but one forgotten "switch off" would take the live site down for weeks.

## Decision

No staging server for now. Changes are checked by these steps instead, in this order:

1. **On every pull request:** the CI tests (API, Web), and the API's Docker image is built and started (`API Docker image`). From M5 PR 13, Playwright clicks through the real app built from the same image.
2. **Before merging:** the change is tried locally, in a real browser for anything users see.
3. **After every merge:** `.github/workflows/after-deploy.yml` waits until Render and Vercel run the merged commit (`/api/health` and the web app's `/version` report it), then checks the live site: API and database healthy, the web app's secret accepted (ADR-025), home and login pages, the login cookie, the not-found page and the safety headers. A failure emails whoever merged. `node .github/scripts/after-deploy-check.mjs` runs the same checks by hand.

Production still updates on every merge to `main`.

**Vercel previews** (the link on each pull request) talk to the live API and database, as before. Only the project owner can open them (Vercel asks them to log in). They are for looking at screens: never book, pay or change settings in a preview, because that changes live data.

## Alternatives considered

- **Pay for the live API** (Render Starter, about US$7 a month): staging could then run free on Render. Revisit when real venues use CourtKu.
- **Staging on Render, switched off when not in use:** free, but one mistake suspends the live site.
- **Another host for staging** (Koyeb, Fly.io, Railway): a card or a trial, and a different platform from production, so it would not test the real set-up anyway.

## Consequences

- No extra cost, and nothing can use up the live site's free hours.
- A bad deploy reaches users before anyone sees it. The after-deploy check notices within minutes, and Render can roll back to the previous deploy with one click (**Events → Rollback**).
- Database migrations run straight on live data, so every migration must work on a database that already has rows, and must be tested that way locally first.
- When CourtKu moves to paid hosting, this ADR should be replaced by a real staging set-up.
