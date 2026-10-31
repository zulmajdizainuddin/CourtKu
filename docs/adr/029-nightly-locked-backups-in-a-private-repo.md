# ADR-029: Nightly locked backups, kept in a private repository, restored every night

- **Status:** Accepted
- **Date:** 2026-11-09

## Context

The live database is a free Aiven MySQL server. Aiven can power free servers off, and the free plan gives us no backup we can download and restore ourselves. If the data were lost, every venue, booking and payment record would go with it. M5 asks for daily backups and one restore that has really worked.

The plan has no budget for storage, the code repository is public, and the database holds personal data (players' names, emails and phone numbers).

## Decision

- **Every night at 3 am Malaysia time**, a GitHub Actions workflow runs `.github/scripts/backup.sh` inside the official `mysql:8.4` Docker image:
  1. `mysqldump` takes one consistent snapshot (`--single-transaction`, no locks on the live site). The rows of `sessions`, `cache` and `cache_locks` are left out: they are logins and throwaway copies, and logins don't belong in a file.
  2. The copy is compressed and **locked with a password** (`gpg`, AES-256). Only the locked file is kept.
  3. The locked file is **unlocked again and restored into an empty MySQL**, exactly as a real restore would.
  4. The restored copy is checked: the same tables as the live database, every migration in the code, and people and venues in it. Only row counts are printed, never data.
- The workflow lives in a **separate private repository** (`courtku-backups`) and fetches the script from this repository each night. Its locked backups are visible only to the owner and are kept for 30 days. A copy of the workflow is in `docs/backups/backup-workflow.yml`.
- The database login and the backup password are GitHub secrets of the private repository. The backup password is also kept in the owner's password manager: without it, no backup can be opened.
- A failed run emails the owner, so a backup that can't be made or can't be restored doesn't go unnoticed.

## Alternatives considered

- **Locked backups in this public repository:** no second repository, but anyone could download the files, so everything would rest on the password alone. Personal data shouldn't be one leaked password away from being public.
- **A cloud storage bucket** (e.g. Cloudflare R2): keeps backups longer, but needs another account and access keys.
- **Backups by hand from the laptop:** easy to forget, and nobody checks that they restore.
- **Aiven's own backups** (paid plans): not on the free plan, and not tested by us.

## Consequences

- There is always a locked copy from the last 30 nights, and every night proves the latest one restores.
- The backup password must never be lost: a backup is useless without it. Changing it only affects new backups.
- The private repository uses about 2 of GitHub's 2,000 free Actions minutes a month for private repositories per night.
- A real restore into the live database is a manual step (docs/deploy.md), done only on purpose.
