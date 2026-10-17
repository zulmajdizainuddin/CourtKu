# ADR-011: Local development on Windows: native PHP, services in Docker

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

The plan assumed Laravel Sail inside WSL2. The developer works on Windows and wants the project in one folder on drive D:. Docker is very slow when it has to read code files from a Windows drive.

## Decision

Run PHP 8.4 (Laravel Herd) and Node.js directly on Windows from the D: folder. Only MySQL, Valkey (Redis) and Mailpit run in Docker (docker-compose.yml): databases don't read the code files, so they stay fast.

## Alternatives considered

- Sail inside WSL2: closest to production, but the code would have to live in the Linux file system instead of D:.

## Consequences

- Fast and simple on Windows.
- Horizon needs Linux (pcntl), so locally we run `php artisan queue:work`; staging and production still use Horizon.
- Windows and Linux differ (file-name case, line endings). .gitattributes forces LF, and CI runs on Linux to catch the rest.
