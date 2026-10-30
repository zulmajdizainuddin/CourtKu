# Architecture decision records

Each file explains one decision: the problem, what we chose, what else we considered, and what it costs us.
To change a decision, add a new ADR that replaces the old one; don't edit history.

| ADR | Decision | Date |
| --- | --- | --- |
| [001](001-separate-api-and-frontend.md) | Separate API and frontend | 2026-10-09 |
| [002](002-shared-database-multi-tenancy.md) | Multi-tenancy with one shared database and a venue_id column | 2026-10-09 |
| [003](003-mysql.md) | MySQL 8 | 2026-10-09 |
| [004](004-sanctum-cookies-one-origin.md) | Sanctum SPA cookie auth, with the web app forwarding all API calls | 2026-10-09 |
| [005](005-slot-rows-unique-index.md) | One row per booked slot with a unique index | 2026-10-09 |
| [006](006-hosted-payment-page-webhook.md) | Hosted payment page, webhook as source of truth | 2026-10-09 |
| [007](007-monorepo.md) | Monorepo with apps/api and apps/web | 2026-10-09 |
| [008](008-rest-v1.md) | REST with versioned routes under /api/v1 | 2026-10-09 |
| [009](009-venue-own-gateway-account.md) | Each venue connects its own payment gateway account | 2026-10-09 |
| [010](010-fixed-30-minute-cells.md) | Store booked time in fixed 30-minute cells | 2026-10-09 |
| [011](011-windows-native-local-dev.md) | Local development on Windows: native PHP, services in Docker | 2026-10-17 |
| [012](012-auth-routes-prefix.md) | Laravel auth endpoints live under /auth | 2026-10-17 |
| [013](013-client-side-route-guard.md) | Protected pages ask the API who is logged in, not the cookie | 2026-10-17 |
| [014](014-multi-sport-courts.md) | A court can offer more than one sport | 2026-10-17 |
| [015](015-pricing-follows-business-day.md) | Pricing rules follow the venue's business day | 2026-10-17 |
| [016](016-two-opening-periods.md) | Up to two opening periods per day | 2026-10-17 |
| [017](017-price-steps.md) | Prices are set in 10-sen steps | 2026-10-17 |
| [018](018-photos-on-cloudinary.md) | Venue photos are stored on Cloudinary | 2026-10-31 |
| [019](019-staff-invites-table.md) | Staff invites have their own table | 2026-10-31 |
| [020](020-price-rules-never-overlap.md) | Price rules never overlap; a court's own rule beats an all-courts rule | 2026-10-31 |
| [021](021-holds-move-to-m4.md) | Checkout holds arrive with online payment (M4), not in M3 | 2026-11-01 |
