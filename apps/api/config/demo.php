<?php

// The demo world (ADR-030): fictional venues full of bookings, so visitors see a busy CourtKu.
// The minute tick keeps it up to date a little at a time (App\Demo\DemoWorld).

return [

    // Off unless switched on (the live site and, if wanted, a laptop): DEMO_WORLD=true.
    'enabled' => (bool) env('DEMO_WORLD', false),

    // The Stripe test account (acct_...) the demo venue that takes online payments uses. CourtKu's
    // own Test Venue already connected one; empty = every demo venue is pay-at-venue only.
    'stripe_account' => env('DEMO_STRIPE_ACCOUNT_ID'),

    // How many days of history to keep, and how far ahead bookings are made (inside the 14-day window).
    'days_back' => (int) env('DEMO_DAYS_BACK', 30),
    'days_ahead' => (int) env('DEMO_DAYS_AHEAD', 13),

    // How long each minute's tick may spend on it, so the cron call stays short.
    'seconds_per_tick' => (float) env('DEMO_SECONDS_PER_TICK', 10),

    // Only the first N venues (tests); null = all of them.
    'venue_limit' => null,

];
