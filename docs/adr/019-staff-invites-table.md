# ADR-019: Staff invites have their own table

- **Status:** Accepted
- **Date:** 2026-10-31

## Context

The plan kept invites on `venue_members` (`invited_at`, `accepted_at`). But a membership row needs a `user_id`, and the person an owner invites (FR-05) often has no CourtKu account yet.

## Decision

Invites live in `venue_invites`: venue, email, role, a hash of the secret code in the link, who invited, `expires_at` and `accepted_at`. Accepting creates the `venue_members` row.

- The link holds a 40-character random code; only its SHA-256 hash is stored, so a copy of the database can't be used to join a venue.
- Links expire after 7 days and work once.
- An invite only works for an account with the invited email address, verified. A forwarded link is useless to anyone else.
- Inviting the same email again replaces the old link.
- `venue_members` keeps only real members, so every membership check stays one simple lookup.

## Alternatives considered

- Invite rows in `venue_members` with an empty `user_id`: every check for "who works here" would have to skip invites, and a missed check would let an invited stranger in.
- Inviting existing accounts only: owners would first have to ask staff to register and tell them their email.

## Consequences

- Owners can invite anyone by email.
- One more table, and the accept step must lock the invite so two clicks at once can't add the person twice.
