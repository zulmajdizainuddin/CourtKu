# ADR-031: Guests can browse the bookable venues on the home page

- **Status:** Accepted
- **Date:** 2026-11-11

## Context

The plan keeps venue discovery out of the MVP (section 1: "not a marketplace"; section 17 lists it for later). Each venue shares its own page, `/v/{slug}`, with its players.

With the demo world (ADR-030) the live site has 20 made-up venues, but a visitor landing on the home page had no way to find any of them. For a portfolio demo, "where do I click to try it?" has to have an answer on the first screen.

## Decision

- The home page lists the venues guests can book: approved (`active`) venues with at least one bookable court. `GET /api/v1/venues` (public, same rate limit as the venue pages) returns 24 a page.
- Each card shows the venue's name, city and state, its sports, a cover photo, and "from RM x an hour": the cheapest court's normal price, for the chosen sport when one is chosen. A venue without photos gets a sample photo of an empty court or hall of its main sport, on its card and its page, so no card is blank. It is tagged "Sample photo" so it is not taken for the venue itself, and the same venue always gets the same one. The 17 photos are free Unsplash photos (Unsplash License, no credit required), each picked to show a place with no people in it, and are loaded straight from Unsplash's image server. An owner's own photos always replace it. Demo venues are not marked in the list; each says so on its own page (ADR-030).
- Visitors can narrow the list by sport and state, and search the venue name or city.
- Real venues come first, then demo venues, each in name order. No ranking, reviews, distance or map.
- Each venue page has an "All venues" link back to the list.

## Alternatives considered

- **Keep discovery out, link a few demo venues in the README:** honest to the plan, but visitors to the site itself would still find nothing to try.
- **A full marketplace** (search by time, map, ratings, featured venues): the after-MVP feature in section 17; far more work than a demo needs.
- **A separate `/venues` page:** the owner preferred everything on the home page.
- **A drawing of the sport's court** for venues without photos: tried first, but it shows no place, so it says nothing about what a venue is like.
- **Photos found on Google Images:** most are copyrighted, and a real place's photo on a made-up venue would mislead.

## Consequences

- A visitor can open the site, pick a venue and book within a minute.
- Venues appear in the list as soon as they are approved and have a priced court, with no opt-out yet; an owner who wants to stay private would need one (a setting, later).
- Sample photos depend on images.unsplash.com staying up and keeping those photos; if one goes away, that card shows a grey box until the list in `apps/web/lib/stock-photo.ts` is updated.
- The list grows with real venues; sorting real ones first keeps them visible when there are many demo venues.
