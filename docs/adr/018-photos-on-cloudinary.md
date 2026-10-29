# ADR-018: Venue photos are stored on Cloudinary

- **Status:** Accepted
- **Date:** 2026-10-31

## Context

Venues need photos (FR-06), and the plan left "choose free photo storage" to M1. The API runs on Render's free plan, whose disk is wiped on every restart, so photos can't be kept on the server. The storage must be free and need no card.

## Decision

Store photos on Cloudinary's free plan (25 credits a month, no card).

- The API uploads each photo to Cloudinary with a signed request; the API secret never leaves the server. Cloudinary picks a random name in a folder per venue.
- Photos are stored at most 2000 pixels on the long side and re-encoded on upload, which also drops metadata such as GPS location.
- The API keeps only Cloudinary's id for each photo. All web addresses are built in one class (`App\Services\Cloudinary`), which asks Cloudinary to resize and compress on delivery (`f_auto,q_auto`).
- The web app shrinks photos in the browser before uploading, because Vercel's proxy refuses request bodies over about 4.5 MB and phone photos are often larger. The API accepts at most 4 MB.

## Alternatives considered

- Backblaze B2 (10 GB free, S3-compatible): public buckets need a small card payment first.
- Supabase Storage (1 GB free): free projects pause after a week without use, like the Aiven database did.
- Render's own disk: not kept between restarts on the free plan.

## Consequences

- Photos load fast from Cloudinary's CDN in a size and format that suits each browser.
- Moving to another storage later means changing one class and copying the files; the database only holds ids.
- Tests never call Cloudinary: they use Laravel's fake HTTP client and check the signed request instead.
- Deleting a photo from Cloudinary is best effort: if it fails, the row is still removed and the leftover file only costs a little storage.
