# ADR-012: Laravel auth endpoints live under /auth

- **Status:** Accepted
- **Date:** 2026-10-17

## Context

The web app has pages at /login, /register, /forgot-password and /reset-password. The plan put Fortify's API endpoints on the same paths, so the Next.js proxy could not tell a page request from an API request.

## Decision

Set Fortify's prefix to `auth`: POST /auth/login, /auth/register, /auth/logout, /auth/forgot-password, /auth/reset-password, and GET /auth/email/verify/{id}/{hash}. Next.js forwards /auth/* to Laravel.

## Alternatives considered

- Keep the API paths and give the web pages different names (e.g. /signin): confusing for users.

## Consequences

- One rewrite rule (/auth/:path*) covers every auth endpoint, including the two-factor routes added in M1.
- The email verification link points at the web app's host and is proxied to Laravel.
