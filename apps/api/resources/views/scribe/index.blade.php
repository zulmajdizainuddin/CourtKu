<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>CourtKu API</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>


    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-account-and-login" class="tocify-header">
                <li class="tocify-item level-1" data-unique="account-and-login">
                    <a href="#account-and-login">Account and login</a>
                </li>
                                    <ul id="tocify-subheader-account-and-login" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-login">
                                <a href="#account-and-login-POSTauth-login">Attempt to authenticate a new session.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-logout">
                                <a href="#account-and-login-POSTauth-logout">Destroy an authenticated session.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-forgot-password">
                                <a href="#account-and-login-POSTauth-forgot-password">Send a reset link to the given user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-reset-password">
                                <a href="#account-and-login-POSTauth-reset-password">Reset the user's password.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-register">
                                <a href="#account-and-login-POSTauth-register">Create a new registered user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETauth-email-verify--id---hash-">
                                <a href="#account-and-login-GETauth-email-verify--id---hash-">Mark the authenticated user's email address as verified.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-email-verification-notification">
                                <a href="#account-and-login-POSTauth-email-verification-notification">Send a new email verification notification.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-PUTauth-user-profile-information">
                                <a href="#account-and-login-PUTauth-user-profile-information">Update the user's profile information.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-PUTauth-user-password">
                                <a href="#account-and-login-PUTauth-user-password">Update the user's password.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETauth-user-confirmed-password-status">
                                <a href="#account-and-login-GETauth-user-confirmed-password-status">Get the password confirmation status.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-user-confirm-password">
                                <a href="#account-and-login-POSTauth-user-confirm-password">Confirm the user's password.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-two-factor-challenge">
                                <a href="#account-and-login-POSTauth-two-factor-challenge">Attempt to authenticate a new session using the two factor authentication code.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-user-two-factor-authentication">
                                <a href="#account-and-login-POSTauth-user-two-factor-authentication">Enable two factor authentication for the user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-user-confirmed-two-factor-authentication">
                                <a href="#account-and-login-POSTauth-user-confirmed-two-factor-authentication">Enable two factor authentication for the user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-DELETEauth-user-two-factor-authentication">
                                <a href="#account-and-login-DELETEauth-user-two-factor-authentication">Disable two factor authentication for the user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETauth-user-two-factor-qr-code">
                                <a href="#account-and-login-GETauth-user-two-factor-qr-code">Get the SVG element for the user's two factor authentication QR code.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETauth-user-two-factor-secret-key">
                                <a href="#account-and-login-GETauth-user-two-factor-secret-key">Get the current user's two factor authentication setup / secret key.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETauth-user-two-factor-recovery-codes">
                                <a href="#account-and-login-GETauth-user-two-factor-recovery-codes">Get the two factor authentication recovery codes for authenticated user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-POSTauth-user-two-factor-recovery-codes">
                                <a href="#account-and-login-POSTauth-user-two-factor-recovery-codes">Generate a fresh set of two factor authentication recovery codes.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETsanctum-csrf-cookie">
                                <a href="#account-and-login-GETsanctum-csrf-cookie">Return an empty response simply to trigger the storage of the CSRF cookie in the browser.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-GETapi-v1-me">
                                <a href="#account-and-login-GETapi-v1-me">GET api/v1/me</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="account-and-login-DELETEapi-v1-me">
                                <a href="#account-and-login-DELETEapi-v1-me">DELETE /api/v1/me  { "password": ".</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-venues-public" class="tocify-header">
                <li class="tocify-item level-1" data-unique="venues-public">
                    <a href="#venues-public">Venues (public)</a>
                </li>
                                    <ul id="tocify-subheader-venues-public" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="venues-public-GETapi-v1-venues">
                                <a href="#venues-public-GETapi-v1-venues">GET /api/v1/venues?sport=badminton&state=selangor&q=cheras&page=2: the venues guests can book,
for the list on the home page (ADR-031). Only approved venues with at least one bookable court.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venues-public-GETapi-v1-venues--venue_slug-">
                                <a href="#venues-public-GETapi-v1-venues--venue_slug-">GET /api/v1/venues/{slug}: everything the venue's public page shows.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venues-public-GETapi-v1-venues--venue_slug--courts">
                                <a href="#venues-public-GETapi-v1-venues--venue_slug--courts">GET /api/v1/venues/{slug}/courts: the courts players can book.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venues-public-GETapi-v1-venues--venue_slug--availability">
                                <a href="#venues-public-GETapi-v1-venues--venue_slug--availability">GET /api/v1/venues/{slug}/availability?date=2026-11-06&sport=badminton
Every bookable court offering the sport, with each half hour players can still book
and its price. Half hours that have started, or are inside the venue's notice, are left out.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venues-public-POSTapi-v1-venues--venue_slug--quote">
                                <a href="#venues-public-POSTapi-v1-venues--venue_slug--quote">POST /api/v1/venues/{slug}/quote: the total for one booking, adding up each half hour.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-bookings-players" class="tocify-header">
                <li class="tocify-item level-1" data-unique="bookings-players">
                    <a href="#bookings-players">Bookings (players)</a>
                </li>
                                    <ul id="tocify-subheader-bookings-players" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="bookings-players-POSTapi-v1-venues--venue_slug--bookings">
                                <a href="#bookings-players-POSTapi-v1-venues--venue_slug--bookings">POST /api/v1/venues/{slug}/bookings  (header Idempotency-Key: <a random id per Book press>)
201 with the new booking; 200 with the same booking when the same key comes again
(a double tap or a network retry); 409 SLOT_TAKEN when someone was faster.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bookings-players-GETapi-v1-bookings--code-">
                                <a href="#bookings-players-GETapi-v1-bookings--code-">GET /api/v1/bookings/{code}: the player's own booking, e.g. for the confirmation page.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bookings-players-GETapi-v1-me-bookings">
                                <a href="#bookings-players-GETapi-v1-me-bookings">GET /api/v1/me/bookings?when=upcoming (soonest first) or ?when=past (latest first, with
cancelled ones), 20 at a time. The next page is ?cursor=<meta.next_cursor> (plan section 8).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bookings-players-POSTapi-v1-bookings--code--cancel">
                                <a href="#bookings-players-POSTapi-v1-bookings--code--cancel">POST /api/v1/bookings/{code}/cancel: the player cancels their own booking (FR-24), until the
venue's cutoff (e.g. 24 hours before). The half hours are free for others straight away.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bookings-players-POSTapi-v1-bookings--code--pay">
                                <a href="#bookings-players-POSTapi-v1-bookings--code--pay">POST /api/v1/bookings/{code}/pay: { "data": { "url": "https://checkout.stripe.com/.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-payments" class="tocify-header">
                <li class="tocify-item level-1" data-unique="payments">
                    <a href="#payments">Payments</a>
                </li>
                                    <ul id="tocify-subheader-payments" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="payments-POSTapi-v1-webhooks-payments-stripe">
                                <a href="#payments-POSTapi-v1-webhooks-payments-stripe">POST api/v1/webhooks/payments/stripe</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-GETapi-v1-manage-venues--venue_id--payment-account">
                                <a href="#payments-GETapi-v1-manage-venues--venue_id--payment-account">GET /api/v1/manage/venues/{venue}/payment-account: the connected account, or null.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">
                                <a href="#payments-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">POST /api/v1/manage/venues/{venue}/payment-account/onboarding: { "data": { "url": "https://connect.stripe.com/.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-DELETEapi-v1-manage-venues--venue_id--payment-account">
                                <a href="#payments-DELETEapi-v1-manage-venues--venue_id--payment-account">DELETE /api/v1/manage/venues/{venue}/payment-account: CourtKu stops using the account, and players
go back to paying at the counter. The Stripe account itself stays the venue's. Refused while a
player is paying, so their payment can still be confirmed.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-PUTapi-v1-manage-venues--venue_id--payment-settings">
                                <a href="#payments-PUTapi-v1-manage-venues--venue_id--payment-settings">PUT /api/v1/manage/venues/{venue}/payment-settings  { "payment_mode": "both", "hold_minutes": 15 }</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-GETapi-v1-manage-venues--venue_id--payments-attention">
                                <a href="#payments-GETapi-v1-manage-venues--venue_id--payments-attention">GET /api/v1/manage/venues/{venue}/payments/attention: online payments the owner should refund (FR-25),
newest first, until they are fully refunded:
  - money that came in but couldn't confirm the booking (wrong amount, too late, after a cancel, paid twice);
  - bookings paid online and cancelled afterwards (by the player before the cutoff, or by staff).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="payments-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">
                                <a href="#payments-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">POST /api/v1/manage/venues/{venue}/payments/{payment}/refunds  { "amount_sen": 2000, "reason": "Paid after the time ran out" }
Part refunds are fine; the total can never be more than was paid. Fully refunded = status refunded.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-venue-setup-owners" class="tocify-header">
                <li class="tocify-item level-1" data-unique="venue-setup-owners">
                    <a href="#venue-setup-owners">Venue setup (owners)</a>
                </li>
                                    <ul id="tocify-subheader-venue-setup-owners" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues">GET /api/v1/manage/venues: the venue picker. Only venues this user belongs to.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues">POST /api/v1/manage/venues: list a new venue (FR-04).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id-">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id-">GET /api/v1/manage/venues/{venue}. The route checks VenuePolicy::view first.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id-">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id-">PUT /api/v1/manage/venues/{venue}: save the profile (FR-06). Owners only (VenuePolicy::update).</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--settings">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--settings">PUT /api/v1/manage/venues/{venue}/settings: how players may book (FR-13). Owners only.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--hours">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--hours">GET /api/v1/manage/venues/{venue}/hours</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--hours">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--hours">PUT /api/v1/manage/venues/{venue}/hours: replaces the whole week.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--closures">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--closures">GET /api/v1/manage/venues/{venue}/closures: today's and future closures, soonest first.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--closures">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues--venue_id--closures">POST /api/v1/manage/venues/{venue}/closures</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">DELETE /api/v1/manage/venues/{venue}/closures/{closure}
The route only finds closures of this venue (scopeBindings), so venue A's URL
can never remove venue B's closure.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--courts">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--courts">GET /api/v1/manage/venues/{venue}/courts: in the owner's chosen order.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--courts">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues--venue_id--courts">POST /api/v1/manage/venues/{venue}/courts: the new court goes at the end of the list.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts-order">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts-order">PUT /api/v1/manage/venues/{venue}/courts/order  { "court_ids": [7, 4, 5] }
Every court of the venue, each exactly once, in the new order.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts--court_id-">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts--court_id-">PUT /api/v1/manage/venues/{venue}/courts/{court}: name, indoor, active and sports.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">DELETE /api/v1/manage/venues/{venue}/courts/{court}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--price-rules">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--price-rules">GET /api/v1/manage/venues/{venue}/price-rules: all-courts rules first, then by start time.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--price-rules">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues--venue_id--price-rules">POST /api/v1/manage/venues/{venue}/price-rules</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">PUT /api/v1/manage/venues/{venue}/price-rules/{priceRule}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">DELETE /api/v1/manage/venues/{venue}/price-rules/{priceRule}: those hours go back to the normal price.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--staff">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--staff">GET /api/v1/manage/venues/{venue}/staff</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--staff-invites">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues--venue_id--staff-invites">POST /api/v1/manage/venues/{venue}/staff-invites  { "email": "aina@example.com" }
Inviting the same email again sends a fresh link; the old one stops working.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">DELETE /api/v1/manage/venues/{venue}/staff-invites/{invite}: the link stops working.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff--user-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff--user-">DELETE /api/v1/manage/venues/{venue}/staff/{user}: they lose access straight away.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-GETapi-v1-manage-venues--venue_id--photos">
                                <a href="#venue-setup-owners-GETapi-v1-manage-venues--venue_id--photos">GET /api/v1/manage/venues/{venue}/photos: in the owner's chosen order.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--photos">
                                <a href="#venue-setup-owners-POSTapi-v1-manage-venues--venue_id--photos">POST /api/v1/manage/venues/{venue}/photos: the new photo goes at the end.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--photos-order">
                                <a href="#venue-setup-owners-PUTapi-v1-manage-venues--venue_id--photos-order">PUT /api/v1/manage/venues/{venue}/photos/order  { "photo_ids": [5, 2, 3] }</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">
                                <a href="#venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">DELETE /api/v1/manage/venues/{venue}/photos/{photo}</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-day-schedule-staff" class="tocify-header">
                <li class="tocify-item level-1" data-unique="day-schedule-staff">
                    <a href="#day-schedule-staff">Day schedule (staff)</a>
                </li>
                                    <ul id="tocify-subheader-day-schedule-staff" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule">
                                <a href="#day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule">GET /api/v1/manage/venues/{venue}/schedule?date=2026-11-06 (today in Malaysia when left out)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule-version">
                                <a href="#day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule-version">GET /api/v1/manage/venues/{venue}/schedule/version (FR-14)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings">POST /api/v1/manage/venues/{venue}/bookings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--blocks">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--blocks">POST /api/v1/manage/venues/{venue}/blocks
{ "court_ids": [6, 7], "starts_at": "2026-11-06T18:00:00+08:00", "minutes": 120, "reason": "Floor repair" }
One block per court, all or nothing: 409 SLOT_TAKEN (with the bookings in the way) when any
of these half hours is already booked or blocked.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">POST .</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">POST .</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">POST .</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">
                                <a href="#day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">POST .</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="day-schedule-staff-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">
                                <a href="#day-schedule-staff-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">DELETE /api/v1/manage/venues/{venue}/blocks/{slotBlock}: the time can be booked again.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-staff-invites" class="tocify-header">
                <li class="tocify-item level-1" data-unique="staff-invites">
                    <a href="#staff-invites">Staff invites</a>
                </li>
                                    <ul id="tocify-subheader-staff-invites" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="staff-invites-GETapi-v1-invites--token-">
                                <a href="#staff-invites-GETapi-v1-invites--token-">GET /api/v1/invites/{token}: what the invite is, before accepting.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="staff-invites-POSTapi-v1-invites--token--accept">
                                <a href="#staff-invites-POSTapi-v1-invites--token--accept">POST /api/v1/invites/{token}/accept: the logged-in, verified user joins the venue.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-reports-owners" class="tocify-header">
                <li class="tocify-item level-1" data-unique="reports-owners">
                    <a href="#reports-owners">Reports (owners)</a>
                </li>
                                    <ul id="tocify-subheader-reports-owners" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="reports-owners-GETapi-v1-manage-venues--venue_id--reports">
                                <a href="#reports-owners-GETapi-v1-manage-venues--venue_id--reports">GET /api/v1/manage/venues/{venue}/reports?from=2026-11-01&to=2026-11-30</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="reports-owners-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">
                                <a href="#reports-owners-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">GET /api/v1/manage/venues/{venue}/reports/bookings.csv?from=2026-11-01&to=2026-11-30</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-admin" class="tocify-header">
                <li class="tocify-item level-1" data-unique="admin">
                    <a href="#admin">Admin</a>
                </li>
                                    <ul id="tocify-subheader-admin" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="admin-GETapi-v1-admin-venues">
                                <a href="#admin-GETapi-v1-admin-venues">GET /api/v1/admin/venues?status=pending: newest first, 25 per page.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="admin-POSTapi-v1-admin-venues--venue_id--approve">
                                <a href="#admin-POSTapi-v1-admin-venues--venue_id--approve">POST /api/v1/admin/venues/{venue}/approve</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="admin-POSTapi-v1-admin-venues--venue_id--suspend">
                                <a href="#admin-POSTapi-v1-admin-venues--venue_id--suspend">POST /api/v1/admin/venues/{venue}/suspend</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="admin-GETapi-v1-admin-stats">
                                <a href="#admin-GETapi-v1-admin-stats">GET api/v1/admin/stats</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-health" class="tocify-header">
                <li class="tocify-item level-1" data-unique="health">
                    <a href="#health">Health</a>
                </li>
                                    <ul id="tocify-subheader-health" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="health-GETapi-health">
                                <a href="#health-GETapi-health">GET api/health</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: November 4, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>The API behind CourtKu: venues, courts, prices, live availability, bookings and online payments for badminton, futsal and pickleball courts in Malaysia.</p>
<aside>
    <strong>Base URL</strong>: <code>https://courtku.vercel.app</code>
</aside>
<pre><code>This is the API the CourtKu web app uses. It is a REST API under `/api/v1` that answers in JSON (ADR-008).

&lt;aside&gt;The live API runs on a free plan that sleeps when nobody uses it: the first request after a quiet period can take up to a minute.&lt;/aside&gt;

**How the web app calls it.** The browser only ever talks to the web app (https://courtku.vercel.app), which forwards `/api`, `/auth` and `/sanctum` to this API. Logins are a normal first-party session cookie from Laravel Sanctum, not tokens (ADR-004), so the examples show the paths as the web app forwards them.

**Logging in.** `GET /sanctum/csrf-cookie` once, then `POST /auth/login` with the `X-XSRF-TOKEN` header. Every request that changes something needs that header. Venue owners and the admin must also pass two-factor login (`POST /auth/two-factor-challenge`).

**Errors** always look the same: `{ "error": { "code": "SLOT_TAKEN", "message": "...", "details": { ... } } }`. A form error is `422 VALIDATION_FAILED` with `details.fields`; a business rule saying no is `409` with its own code.

**Times** are sent and returned with the Malaysia offset, e.g. `2026-11-06T20:00:00+08:00`. Money is in sen (`2000` = RM 20.00), with a ready-made `"RM 20.00"` text next to it.

The source and the decisions behind it: https://github.com/zulmajdizainuddin/CourtKu</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include a <strong><code>X-XSRF-TOKEN</code></strong> header with the value <strong><code>"{XSRF-TOKEN cookie value}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>CourtKu uses a login session cookie, not API tokens (ADR-004). Call <code>GET /sanctum/csrf-cookie</code>, then <code>POST /auth/login</code>; the browser keeps the session cookie, and every request that changes something sends the <code>XSRF-TOKEN</code> cookie back in the <code>X-XSRF-TOKEN</code> header. Owners and the admin also need two-factor login.</p>

        <h1 id="account-and-login">Account and login</h1>

    

                                <h2 id="account-and-login-POSTauth-login">Attempt to authenticate a new session.</h2>

<p>
</p>



<span id="example-requests-POSTauth-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"gbailey@example.net\",
    \"password\": \"|]|{+-\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "gbailey@example.net",
    "password": "|]|{+-"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-login">
</span>
<span id="execution-results-POSTauth-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-login" data-method="POST"
      data-path="auth/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTauth-login"
               value="gbailey@example.net"
               data-component="body">
    <br>
<p>Example: <code>gbailey@example.net</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTauth-login"
               value="|]|{+-"
               data-component="body">
    <br>
<p>Example: <code>|]|{+-</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>remember</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="remember"                data-endpoint="POSTauth-login"
               value=""
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="account-and-login-POSTauth-logout">Destroy an authenticated session.</h2>

<p>
</p>



<span id="example-requests-POSTauth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-logout">
</span>
<span id="execution-results-POSTauth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-logout" data-method="POST"
      data-path="auth/logout"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-POSTauth-forgot-password">Send a reset link to the given user.</h2>

<p>
</p>



<span id="example-requests-POSTauth-forgot-password">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/forgot-password" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"gbailey@example.net\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/forgot-password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "gbailey@example.net"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-forgot-password">
</span>
<span id="execution-results-POSTauth-forgot-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-forgot-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-forgot-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-forgot-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-forgot-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-forgot-password" data-method="POST"
      data-path="auth/forgot-password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-forgot-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/forgot-password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-forgot-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-forgot-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTauth-forgot-password"
               value="gbailey@example.net"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>gbailey@example.net</code></p>
        </div>
        </form>

                    <h2 id="account-and-login-POSTauth-reset-password">Reset the user&#039;s password.</h2>

<p>
</p>



<span id="example-requests-POSTauth-reset-password">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/reset-password" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"token\": \"architecto\",
    \"password\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/reset-password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "token": "architecto",
    "password": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-reset-password">
</span>
<span id="execution-results-POSTauth-reset-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-reset-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-reset-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-reset-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-reset-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-reset-password" data-method="POST"
      data-path="auth/reset-password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-reset-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/reset-password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-reset-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-reset-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTauth-reset-password"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTauth-reset-password"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="account-and-login-POSTauth-register">Create a new registered user.</h2>

<p>
</p>



<span id="example-requests-POSTauth-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-register">
</span>
<span id="execution-results-POSTauth-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-register" data-method="POST"
      data-path="auth/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETauth-email-verify--id---hash-">Mark the authenticated user&#039;s email address as verified.</h2>

<p>
</p>



<span id="example-requests-GETauth-email-verify--id---hash-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/auth/email/verify/architecto/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/email/verify/architecto/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETauth-email-verify--id---hash-">
    </span>
<span id="execution-results-GETauth-email-verify--id---hash-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETauth-email-verify--id---hash-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETauth-email-verify--id---hash-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETauth-email-verify--id---hash-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETauth-email-verify--id---hash-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETauth-email-verify--id---hash-" data-method="GET"
      data-path="auth/email/verify/{id}/{hash}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETauth-email-verify--id---hash-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>auth/email/verify/{id}/{hash}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETauth-email-verify--id---hash-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETauth-email-verify--id---hash-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETauth-email-verify--id---hash-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the verify. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>hash</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="hash"                data-endpoint="GETauth-email-verify--id---hash-"
               value="architecto"
               data-component="url">
    <br>
<p>Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="account-and-login-POSTauth-email-verification-notification">Send a new email verification notification.</h2>

<p>
</p>



<span id="example-requests-POSTauth-email-verification-notification">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/email/verification-notification" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/email/verification-notification"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-email-verification-notification">
</span>
<span id="execution-results-POSTauth-email-verification-notification" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-email-verification-notification"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-email-verification-notification"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-email-verification-notification" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-email-verification-notification">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-email-verification-notification" data-method="POST"
      data-path="auth/email/verification-notification"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-email-verification-notification', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/email/verification-notification</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-email-verification-notification"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-email-verification-notification"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-PUTauth-user-profile-information">Update the user&#039;s profile information.</h2>

<p>
</p>



<span id="example-requests-PUTauth-user-profile-information">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/auth/user/profile-information" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/profile-information"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "PUT",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTauth-user-profile-information">
</span>
<span id="execution-results-PUTauth-user-profile-information" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTauth-user-profile-information"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTauth-user-profile-information"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTauth-user-profile-information" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTauth-user-profile-information">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTauth-user-profile-information" data-method="PUT"
      data-path="auth/user/profile-information"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTauth-user-profile-information', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>auth/user/profile-information</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTauth-user-profile-information"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTauth-user-profile-information"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-PUTauth-user-password">Update the user&#039;s password.</h2>

<p>
</p>



<span id="example-requests-PUTauth-user-password">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/auth/user/password" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "PUT",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTauth-user-password">
</span>
<span id="execution-results-PUTauth-user-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTauth-user-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTauth-user-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTauth-user-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTauth-user-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTauth-user-password" data-method="PUT"
      data-path="auth/user/password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTauth-user-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>auth/user/password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTauth-user-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTauth-user-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETauth-user-confirmed-password-status">Get the password confirmation status.</h2>

<p>
</p>



<span id="example-requests-GETauth-user-confirmed-password-status">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/auth/user/confirmed-password-status" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/confirmed-password-status"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETauth-user-confirmed-password-status">
    </span>
<span id="execution-results-GETauth-user-confirmed-password-status" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETauth-user-confirmed-password-status"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETauth-user-confirmed-password-status"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETauth-user-confirmed-password-status" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETauth-user-confirmed-password-status">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETauth-user-confirmed-password-status" data-method="GET"
      data-path="auth/user/confirmed-password-status"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETauth-user-confirmed-password-status', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>auth/user/confirmed-password-status</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETauth-user-confirmed-password-status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETauth-user-confirmed-password-status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-POSTauth-user-confirm-password">Confirm the user&#039;s password.</h2>

<p>
</p>



<span id="example-requests-POSTauth-user-confirm-password">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/user/confirm-password" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/confirm-password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-user-confirm-password">
</span>
<span id="execution-results-POSTauth-user-confirm-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-user-confirm-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-user-confirm-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-user-confirm-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-user-confirm-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-user-confirm-password" data-method="POST"
      data-path="auth/user/confirm-password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-user-confirm-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/user/confirm-password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-user-confirm-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-user-confirm-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-POSTauth-two-factor-challenge">Attempt to authenticate a new session using the two factor authentication code.</h2>

<p>
</p>



<span id="example-requests-POSTauth-two-factor-challenge">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/two-factor-challenge" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"code\": \"architecto\",
    \"recovery_code\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/two-factor-challenge"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "code": "architecto",
    "recovery_code": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-two-factor-challenge">
</span>
<span id="execution-results-POSTauth-two-factor-challenge" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-two-factor-challenge"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-two-factor-challenge"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-two-factor-challenge" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-two-factor-challenge">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-two-factor-challenge" data-method="POST"
      data-path="auth/two-factor-challenge"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-two-factor-challenge', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/two-factor-challenge</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-two-factor-challenge"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-two-factor-challenge"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTauth-two-factor-challenge"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>recovery_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="recovery_code"                data-endpoint="POSTauth-two-factor-challenge"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="account-and-login-POSTauth-user-two-factor-authentication">Enable two factor authentication for the user.</h2>

<p>
</p>



<span id="example-requests-POSTauth-user-two-factor-authentication">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/user/two-factor-authentication" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-authentication"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-user-two-factor-authentication">
</span>
<span id="execution-results-POSTauth-user-two-factor-authentication" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-user-two-factor-authentication"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-user-two-factor-authentication"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-user-two-factor-authentication" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-user-two-factor-authentication">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-user-two-factor-authentication" data-method="POST"
      data-path="auth/user/two-factor-authentication"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-user-two-factor-authentication', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/user/two-factor-authentication</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-user-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-user-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-POSTauth-user-confirmed-two-factor-authentication">Enable two factor authentication for the user.</h2>

<p>
</p>



<span id="example-requests-POSTauth-user-confirmed-two-factor-authentication">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/user/confirmed-two-factor-authentication" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/confirmed-two-factor-authentication"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-user-confirmed-two-factor-authentication">
</span>
<span id="execution-results-POSTauth-user-confirmed-two-factor-authentication" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-user-confirmed-two-factor-authentication"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-user-confirmed-two-factor-authentication"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-user-confirmed-two-factor-authentication" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-user-confirmed-two-factor-authentication">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-user-confirmed-two-factor-authentication" data-method="POST"
      data-path="auth/user/confirmed-two-factor-authentication"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-user-confirmed-two-factor-authentication', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/user/confirmed-two-factor-authentication</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-user-confirmed-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-user-confirmed-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-DELETEauth-user-two-factor-authentication">Disable two factor authentication for the user.</h2>

<p>
</p>



<span id="example-requests-DELETEauth-user-two-factor-authentication">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/auth/user/two-factor-authentication" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-authentication"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEauth-user-two-factor-authentication">
</span>
<span id="execution-results-DELETEauth-user-two-factor-authentication" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEauth-user-two-factor-authentication"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEauth-user-two-factor-authentication"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEauth-user-two-factor-authentication" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEauth-user-two-factor-authentication">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEauth-user-two-factor-authentication" data-method="DELETE"
      data-path="auth/user/two-factor-authentication"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEauth-user-two-factor-authentication', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>auth/user/two-factor-authentication</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEauth-user-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEauth-user-two-factor-authentication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETauth-user-two-factor-qr-code">Get the SVG element for the user&#039;s two factor authentication QR code.</h2>

<p>
</p>



<span id="example-requests-GETauth-user-two-factor-qr-code">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/auth/user/two-factor-qr-code" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-qr-code"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETauth-user-two-factor-qr-code">
    </span>
<span id="execution-results-GETauth-user-two-factor-qr-code" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETauth-user-two-factor-qr-code"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETauth-user-two-factor-qr-code"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETauth-user-two-factor-qr-code" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETauth-user-two-factor-qr-code">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETauth-user-two-factor-qr-code" data-method="GET"
      data-path="auth/user/two-factor-qr-code"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETauth-user-two-factor-qr-code', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>auth/user/two-factor-qr-code</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETauth-user-two-factor-qr-code"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETauth-user-two-factor-qr-code"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETauth-user-two-factor-secret-key">Get the current user&#039;s two factor authentication setup / secret key.</h2>

<p>
</p>



<span id="example-requests-GETauth-user-two-factor-secret-key">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/auth/user/two-factor-secret-key" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-secret-key"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETauth-user-two-factor-secret-key">
    </span>
<span id="execution-results-GETauth-user-two-factor-secret-key" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETauth-user-two-factor-secret-key"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETauth-user-two-factor-secret-key"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETauth-user-two-factor-secret-key" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETauth-user-two-factor-secret-key">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETauth-user-two-factor-secret-key" data-method="GET"
      data-path="auth/user/two-factor-secret-key"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETauth-user-two-factor-secret-key', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>auth/user/two-factor-secret-key</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETauth-user-two-factor-secret-key"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETauth-user-two-factor-secret-key"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETauth-user-two-factor-recovery-codes">Get the two factor authentication recovery codes for authenticated user.</h2>

<p>
</p>



<span id="example-requests-GETauth-user-two-factor-recovery-codes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/auth/user/two-factor-recovery-codes" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-recovery-codes"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETauth-user-two-factor-recovery-codes">
    </span>
<span id="execution-results-GETauth-user-two-factor-recovery-codes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETauth-user-two-factor-recovery-codes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETauth-user-two-factor-recovery-codes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETauth-user-two-factor-recovery-codes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETauth-user-two-factor-recovery-codes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETauth-user-two-factor-recovery-codes" data-method="GET"
      data-path="auth/user/two-factor-recovery-codes"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETauth-user-two-factor-recovery-codes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>auth/user/two-factor-recovery-codes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETauth-user-two-factor-recovery-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETauth-user-two-factor-recovery-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-POSTauth-user-two-factor-recovery-codes">Generate a fresh set of two factor authentication recovery codes.</h2>

<p>
</p>



<span id="example-requests-POSTauth-user-two-factor-recovery-codes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/auth/user/two-factor-recovery-codes" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/auth/user/two-factor-recovery-codes"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTauth-user-two-factor-recovery-codes">
</span>
<span id="execution-results-POSTauth-user-two-factor-recovery-codes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTauth-user-two-factor-recovery-codes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTauth-user-two-factor-recovery-codes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTauth-user-two-factor-recovery-codes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTauth-user-two-factor-recovery-codes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTauth-user-two-factor-recovery-codes" data-method="POST"
      data-path="auth/user/two-factor-recovery-codes"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTauth-user-two-factor-recovery-codes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>auth/user/two-factor-recovery-codes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTauth-user-two-factor-recovery-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTauth-user-two-factor-recovery-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETsanctum-csrf-cookie">Return an empty response simply to trigger the storage of the CSRF cookie in the browser.</h2>

<p>
</p>



<span id="example-requests-GETsanctum-csrf-cookie">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/sanctum/csrf-cookie" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/sanctum/csrf-cookie"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETsanctum-csrf-cookie">
    </span>
<span id="execution-results-GETsanctum-csrf-cookie" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETsanctum-csrf-cookie"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETsanctum-csrf-cookie"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETsanctum-csrf-cookie" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETsanctum-csrf-cookie">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETsanctum-csrf-cookie" data-method="GET"
      data-path="sanctum/csrf-cookie"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETsanctum-csrf-cookie', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>sanctum/csrf-cookie</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETsanctum-csrf-cookie"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETsanctum-csrf-cookie"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-GETapi-v1-me">GET api/v1/me</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/me" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/me"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me">
    </span>
<span id="execution-results-GETapi-v1-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me" data-method="GET"
      data-path="api/v1/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-me"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="account-and-login-DELETEapi-v1-me">DELETE /api/v1/me  { &quot;password&quot;: &quot;.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>.." }: delete my own account (NFR-07, ADR-033).
The password is asked again, so someone at an unlocked laptop can't do it in one click.</p>

<span id="example-requests-DELETEapi-v1-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/me" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"password\": \"|]|{+-\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/me"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "password": "|]|{+-"
};

fetch(url, {
    method: "DELETE",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-me">
</span>
<span id="execution-results-DELETEapi-v1-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-me" data-method="DELETE"
      data-path="api/v1/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-me"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="DELETEapi-v1-me"
               value="|]|{+-"
               data-component="body">
    <br>
<p>Example: <code>|]|{+-</code></p>
        </div>
        </form>

                <h1 id="venues-public">Venues (public)</h1>

    

                                <h2 id="venues-public-GETapi-v1-venues">GET /api/v1/venues?sport=badminton&amp;state=selangor&amp;q=cheras&amp;page=2: the venues guests can book,
for the list on the home page (ADR-031). Only approved venues with at least one bookable court.</h2>

<p>
</p>

<p>Real venues come first, then the demo venues (ADR-030); 24 per page.</p>

<span id="example-requests-GETapi-v1-venues">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/venues" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"sport\": \"architecto\",
    \"state\": \"melaka\",
    \"q\": \"n\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "sport": "architecto",
    "state": "melaka",
    "q": "n"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-venues">
    </span>
<span id="execution-results-GETapi-v1-venues" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-venues"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-venues"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-venues" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-venues">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-venues" data-method="GET"
      data-path="api/v1/venues"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-venues', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/venues</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sport</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sport"                data-endpoint="GETapi-v1-venues"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="state"                data-endpoint="GETapi-v1-venues"
               value="melaka"
               data-component="body">
    <br>
<p>Example: <code>melaka</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>johor</code></li> <li><code>kedah</code></li> <li><code>kelantan</code></li> <li><code>melaka</code></li> <li><code>negeri-sembilan</code></li> <li><code>pahang</code></li> <li><code>perak</code></li> <li><code>perlis</code></li> <li><code>pulau-pinang</code></li> <li><code>sabah</code></li> <li><code>sarawak</code></li> <li><code>selangor</code></li> <li><code>terengganu</code></li> <li><code>kuala-lumpur</code></li> <li><code>labuan</code></li> <li><code>putrajaya</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>q</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="q"                data-endpoint="GETapi-v1-venues"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 60 characters. Example: <code>n</code></p>
        </div>
        </form>

                    <h2 id="venues-public-GETapi-v1-venues--venue_slug-">GET /api/v1/venues/{slug}: everything the venue&#039;s public page shows.</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-venues--venue_slug-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/venues/arena-demo" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues/arena-demo"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-venues--venue_slug-">
    </span>
<span id="execution-results-GETapi-v1-venues--venue_slug-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-venues--venue_slug-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-venues--venue_slug-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-venues--venue_slug-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-venues--venue_slug-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-venues--venue_slug-" data-method="GET"
      data-path="api/v1/venues/{venue_slug}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-venues--venue_slug-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/venues/{venue_slug}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-venues--venue_slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-venues--venue_slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="venue_slug"                data-endpoint="GETapi-v1-venues--venue_slug-"
               value="arena-demo"
               data-component="url">
    <br>
<p>The venue's web address name (slug). Example: <code>arena-demo</code></p>
            </div>
                    </form>

                    <h2 id="venues-public-GETapi-v1-venues--venue_slug--courts">GET /api/v1/venues/{slug}/courts: the courts players can book.</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-venues--venue_slug--courts">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/venues/arena-demo/courts" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues/arena-demo/courts"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-venues--venue_slug--courts">
    </span>
<span id="execution-results-GETapi-v1-venues--venue_slug--courts" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-venues--venue_slug--courts"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-venues--venue_slug--courts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-venues--venue_slug--courts" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-venues--venue_slug--courts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-venues--venue_slug--courts" data-method="GET"
      data-path="api/v1/venues/{venue_slug}/courts"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-venues--venue_slug--courts', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/venues/{venue_slug}/courts</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-venues--venue_slug--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-venues--venue_slug--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="venue_slug"                data-endpoint="GETapi-v1-venues--venue_slug--courts"
               value="arena-demo"
               data-component="url">
    <br>
<p>The venue's web address name (slug). Example: <code>arena-demo</code></p>
            </div>
                    </form>

                    <h2 id="venues-public-GETapi-v1-venues--venue_slug--availability">GET /api/v1/venues/{slug}/availability?date=2026-11-06&amp;sport=badminton
Every bookable court offering the sport, with each half hour players can still book
and its price. Half hours that have started, or are inside the venue&#039;s notice, are left out.</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-venues--venue_slug--availability">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/venues/arena-demo/availability" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"date\": \"2026-11-04\",
    \"sport\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues/arena-demo/availability"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "date": "2026-11-04",
    "sport": "architecto"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-venues--venue_slug--availability">
    </span>
<span id="execution-results-GETapi-v1-venues--venue_slug--availability" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-venues--venue_slug--availability"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-venues--venue_slug--availability"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-venues--venue_slug--availability" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-venues--venue_slug--availability">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-venues--venue_slug--availability" data-method="GET"
      data-path="api/v1/venues/{venue_slug}/availability"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-venues--venue_slug--availability', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/venues/{venue_slug}/availability</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-venues--venue_slug--availability"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-venues--venue_slug--availability"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="venue_slug"                data-endpoint="GETapi-v1-venues--venue_slug--availability"
               value="arena-demo"
               data-component="url">
    <br>
<p>The venue's web address name (slug). Example: <code>arena-demo</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date"                data-endpoint="GETapi-v1-venues--venue_slug--availability"
               value="2026-11-04"
               data-component="body">
    <br>
<p>Must be a valid date in the format <code>Y-m-d</code>. Example: <code>2026-11-04</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sport</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sport"                data-endpoint="GETapi-v1-venues--venue_slug--availability"
               value="architecto"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="venues-public-POSTapi-v1-venues--venue_slug--quote">POST /api/v1/venues/{slug}/quote: the total for one booking, adding up each half hour.</h2>

<p>
</p>

<p>The booking page shows this before the player books; M3 uses the same check to book.</p>

<span id="example-requests-POSTapi-v1-venues--venue_slug--quote">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/venues/arena-demo/quote" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"court_id\": 16,
    \"starts_at\": \"2026-11-04T15:46:09\",
    \"minutes\": 61
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues/arena-demo/quote"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "court_id": 16,
    "starts_at": "2026-11-04T15:46:09",
    "minutes": 61
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-venues--venue_slug--quote">
</span>
<span id="execution-results-POSTapi-v1-venues--venue_slug--quote" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-venues--venue_slug--quote"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-venues--venue_slug--quote"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-venues--venue_slug--quote" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-venues--venue_slug--quote">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-venues--venue_slug--quote" data-method="POST"
      data-path="api/v1/venues/{venue_slug}/quote"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-venues--venue_slug--quote', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/venues/{venue_slug}/quote</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="venue_slug"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="arena-demo"
               data-component="url">
    <br>
<p>The venue's web address name (slug). Example: <code>arena-demo</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="16"
               data-component="body">
    <br>
<p>Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="2026-11-04T15:46:09"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-11-04T15:46:09</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="minutes"                data-endpoint="POSTapi-v1-venues--venue_slug--quote"
               value="61"
               data-component="body">
    <br>
<p>Must be between 60 and 180. Example: <code>61</code></p>
        </div>
        </form>

                <h1 id="bookings-players">Bookings (players)</h1>

    

                                <h2 id="bookings-players-POSTapi-v1-venues--venue_slug--bookings">POST /api/v1/venues/{slug}/bookings  (header Idempotency-Key: &lt;a random id per Book press&gt;)
201 with the new booking; 200 with the same booking when the same key comes again
(a double tap or a network retry); 409 SLOT_TAKEN when someone was faster.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-venues--venue_slug--bookings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/venues/arena-demo/bookings" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"court_id\": 16,
    \"sport\": \"architecto\",
    \"starts_at\": \"2026-11-04T15:46:09\",
    \"minutes\": 22,
    \"pay\": \"online\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/venues/arena-demo/bookings"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "court_id": 16,
    "sport": "architecto",
    "starts_at": "2026-11-04T15:46:09",
    "minutes": 22,
    "pay": "online"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-venues--venue_slug--bookings">
</span>
<span id="execution-results-POSTapi-v1-venues--venue_slug--bookings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-venues--venue_slug--bookings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-venues--venue_slug--bookings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-venues--venue_slug--bookings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-venues--venue_slug--bookings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-venues--venue_slug--bookings" data-method="POST"
      data-path="api/v1/venues/{venue_slug}/bookings"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-venues--venue_slug--bookings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/venues/{venue_slug}/bookings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="venue_slug"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="arena-demo"
               data-component="url">
    <br>
<p>The venue's web address name (slug). Example: <code>arena-demo</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="16"
               data-component="body">
    <br>
<p>Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sport</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sport"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="architecto"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="2026-11-04T15:46:09"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-11-04T15:46:09</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="minutes"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="22"
               data-component="body">
    <br>
<p>Must be at least 30. Must not be greater than 720. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="pay"                data-endpoint="POSTapi-v1-venues--venue_slug--bookings"
               value="online"
               data-component="body">
    <br>
<p>Example: <code>online</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>online</code></li> <li><code>counter</code></li></ul>
        </div>
        </form>

                    <h2 id="bookings-players-GETapi-v1-bookings--code-">GET /api/v1/bookings/{code}: the player&#039;s own booking, e.g. for the confirmation page.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>While it waits for payment, CourtKu asks Stripe how the payment stands first (at most every few
seconds), so the status page shows "paid" as soon as Stripe knows, webhook or not.</p>

<span id="example-requests-GETapi-v1-bookings--code-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-bookings--code-">
    </span>
<span id="execution-results-GETapi-v1-bookings--code-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-bookings--code-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-bookings--code-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-bookings--code-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-bookings--code-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-bookings--code-" data-method="GET"
      data-path="api/v1/bookings/{code}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-bookings--code-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/bookings/{code}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-bookings--code-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-bookings--code-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-bookings--code-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="GETapi-v1-bookings--code-"
               value="CK-7F3K2"
               data-component="url">
    <br>
<p>The booking code. Example: <code>CK-7F3K2</code></p>
            </div>
                    </form>

                    <h2 id="bookings-players-GETapi-v1-me-bookings">GET /api/v1/me/bookings?when=upcoming (soonest first) or ?when=past (latest first, with
cancelled ones), 20 at a time. The next page is ?cursor=&lt;meta.next_cursor&gt; (plan section 8).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-me-bookings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/me/bookings" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/me/bookings"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-bookings">
    </span>
<span id="execution-results-GETapi-v1-me-bookings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-bookings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-bookings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-bookings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-bookings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-bookings" data-method="GET"
      data-path="api/v1/me/bookings"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-bookings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/bookings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-me-bookings"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="bookings-players-POSTapi-v1-bookings--code--cancel">POST /api/v1/bookings/{code}/cancel: the player cancels their own booking (FR-24), until the
venue&#039;s cutoff (e.g. 24 hours before). The half hours are free for others straight away.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-bookings--code--cancel">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2/cancel" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2/cancel"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-bookings--code--cancel">
</span>
<span id="execution-results-POSTapi-v1-bookings--code--cancel" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-bookings--code--cancel"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-bookings--code--cancel"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-bookings--code--cancel" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-bookings--code--cancel">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-bookings--code--cancel" data-method="POST"
      data-path="api/v1/bookings/{code}/cancel"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-bookings--code--cancel', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/bookings/{code}/cancel</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-bookings--code--cancel"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-bookings--code--cancel"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-bookings--code--cancel"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-v1-bookings--code--cancel"
               value="CK-7F3K2"
               data-component="url">
    <br>
<p>The booking code. Example: <code>CK-7F3K2</code></p>
            </div>
                    </form>

                    <h2 id="bookings-players-POSTapi-v1-bookings--code--pay">POST /api/v1/bookings/{code}/pay: { &quot;data&quot;: { &quot;url&quot;: &quot;https://checkout.stripe.com/.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>.." } }
Where the player pays for a booking that is waiting for payment: the page already open, or a new
one after a declined card. 409 NOT_AWAITING_PAYMENT once it is paid, cancelled or expired.</p>

<span id="example-requests-POSTapi-v1-bookings--code--pay">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2/pay" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/bookings/CK-7F3K2/pay"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-bookings--code--pay">
</span>
<span id="execution-results-POSTapi-v1-bookings--code--pay" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-bookings--code--pay"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-bookings--code--pay"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-bookings--code--pay" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-bookings--code--pay">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-bookings--code--pay" data-method="POST"
      data-path="api/v1/bookings/{code}/pay"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-bookings--code--pay', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/bookings/{code}/pay</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-bookings--code--pay"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-bookings--code--pay"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-bookings--code--pay"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-v1-bookings--code--pay"
               value="CK-7F3K2"
               data-component="url">
    <br>
<p>The booking code. Example: <code>CK-7F3K2</code></p>
            </div>
                    </form>

                <h1 id="payments">Payments</h1>

    

                                <h2 id="payments-POSTapi-v1-webhooks-payments-stripe">POST api/v1/webhooks/payments/stripe</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-webhooks-payments-stripe">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/webhooks/payments/stripe" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/webhooks/payments/stripe"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-webhooks-payments-stripe">
</span>
<span id="execution-results-POSTapi-v1-webhooks-payments-stripe" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-webhooks-payments-stripe"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-webhooks-payments-stripe"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-webhooks-payments-stripe" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-webhooks-payments-stripe">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-webhooks-payments-stripe" data-method="POST"
      data-path="api/v1/webhooks/payments/stripe"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-webhooks-payments-stripe', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/webhooks/payments/stripe</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-webhooks-payments-stripe"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-webhooks-payments-stripe"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="payments-GETapi-v1-manage-venues--venue_id--payment-account">GET /api/v1/manage/venues/{venue}/payment-account: the connected account, or null.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Until it can take payments, CourtKu asks Stripe again each time, e.g. when the owner comes back from Stripe.</p>

<span id="example-requests-GETapi-v1-manage-venues--venue_id--payment-account">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--payment-account">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--payment-account" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--payment-account"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--payment-account"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--payment-account" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--payment-account">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--payment-account" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/payment-account"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--payment-account', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/payment-account</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--payment-account"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--payment-account"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--payment-account"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--payment-account"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="payments-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">POST /api/v1/manage/venues/{venue}/payment-account/onboarding: { &quot;data&quot;: { &quot;url&quot;: &quot;https://connect.stripe.com/.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>.." } }
Where the owner goes next: Stripe's own sign-up pages for the venue's account. The first time,
the account is made first. Each link works once and for a few minutes, so a new one is made each time.</p>

<span id="example-requests-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account/onboarding" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account/onboarding"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--payment-account-onboarding" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/payment-account/onboarding"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--payment-account-onboarding', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/payment-account/onboarding</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payment-account-onboarding"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="payments-DELETEapi-v1-manage-venues--venue_id--payment-account">DELETE /api/v1/manage/venues/{venue}/payment-account: CourtKu stops using the account, and players
go back to paying at the counter. The Stripe account itself stays the venue&#039;s. Refused while a
player is paying, so their payment can still be confirmed.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--payment-account">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-account"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--payment-account">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--payment-account" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--payment-account"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--payment-account"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--payment-account" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--payment-account">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--payment-account" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/payment-account"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--payment-account', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/payment-account</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--payment-account"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--payment-account"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--payment-account"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--payment-account"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="payments-PUTapi-v1-manage-venues--venue_id--payment-settings">PUT /api/v1/manage/venues/{venue}/payment-settings  { &quot;payment_mode&quot;: &quot;both&quot;, &quot;hold_minutes&quot;: 15 }</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--payment-settings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-settings" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"payment_mode\": \"counter\",
    \"hold_minutes\": 15
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payment-settings"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "payment_mode": "counter",
    "hold_minutes": 15
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--payment-settings">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--payment-settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--payment-settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--payment-settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--payment-settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--payment-settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--payment-settings" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/payment-settings"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--payment-settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/payment-settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>payment_mode</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="payment_mode"                data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="counter"
               data-component="body">
    <br>
<p>Example: <code>counter</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>counter</code></li> <li><code>online</code></li> <li><code>both</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>hold_minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="hold_minutes"                data-endpoint="PUTapi-v1-manage-venues--venue_id--payment-settings"
               value="15"
               data-component="body">
    <br>
<p>Example: <code>15</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>10</code></li> <li><code>15</code></li> <li><code>20</code></li> <li><code>30</code></li></ul>
        </div>
        </form>

                    <h2 id="payments-GETapi-v1-manage-venues--venue_id--payments-attention">GET /api/v1/manage/venues/{venue}/payments/attention: online payments the owner should refund (FR-25),
newest first, until they are fully refunded:
  - money that came in but couldn&#039;t confirm the booking (wrong amount, too late, after a cancel, paid twice);
  - bookings paid online and cancelled afterwards (by the player before the cutoff, or by staff).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--payments-attention">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/payments/attention" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payments/attention"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--payments-attention">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--payments-attention" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--payments-attention"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--payments-attention"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--payments-attention" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--payments-attention">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--payments-attention" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/payments/attention"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--payments-attention', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/payments/attention</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--payments-attention"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--payments-attention"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--payments-attention"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--payments-attention"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="payments-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">POST /api/v1/manage/venues/{venue}/payments/{payment}/refunds  { &quot;amount_sen&quot;: 2000, &quot;reason&quot;: &quot;Paid after the time ran out&quot; }
Part refunds are fine; the total can never be more than was paid. Fully refunded = status refunded.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/payments/2/refunds" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"amount_sen\": 16,
    \"reason\": \"n\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/payments/2/refunds"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "amount_sen": 16,
    "reason": "n"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/payments/{payment_id}/refunds"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/payments/{payment_id}/refunds</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>payment_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="payment_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="2"
               data-component="url">
    <br>
<p>The ID of the payment. Example: <code>2</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount_sen</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount_sen"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="16"
               data-component="body">
    <br>
<p>Must be at least 1. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-v1-manage-venues--venue_id--payments--payment_id--refunds"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 200 characters. Example: <code>n</code></p>
        </div>
        </form>

                <h1 id="venue-setup-owners">Venue setup (owners)</h1>

    

                                <h2 id="venue-setup-owners-GETapi-v1-manage-venues">GET /api/v1/manage/venues: the venue picker. Only venues this user belongs to.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues">
    </span>
<span id="execution-results-GETapi-v1-manage-venues" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues" data-method="GET"
      data-path="api/v1/manage/venues"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues">POST /api/v1/manage/venues: list a new venue (FR-04).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>It starts as pending and stays hidden until an admin approves it.</p>

<span id="example-requests-POSTapi-v1-manage-venues">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"slug\": \"n\",
    \"state\": \"putrajaya\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "slug": "n",
    "state": "putrajaya"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues">
</span>
<span id="execution-results-POSTapi-v1-manage-venues" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues" data-method="POST"
      data-path="api/v1/manage/venues"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-manage-venues"
               value="b"
               data-component="body">
    <br>
<p>Must be at least 3 characters. Must not be greater than 100 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="slug"                data-endpoint="POSTapi-v1-manage-venues"
               value="n"
               data-component="body">
    <br>
<p>Must match the regex /^[a-z0-9]+(?:-[a-z0-9]+)*$/. Must be at least 3 characters. Must not be greater than 60 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="state"                data-endpoint="POSTapi-v1-manage-venues"
               value="putrajaya"
               data-component="body">
    <br>
<p>Example: <code>putrajaya</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>johor</code></li> <li><code>kedah</code></li> <li><code>kelantan</code></li> <li><code>melaka</code></li> <li><code>negeri-sembilan</code></li> <li><code>pahang</code></li> <li><code>perak</code></li> <li><code>perlis</code></li> <li><code>pulau-pinang</code></li> <li><code>sabah</code></li> <li><code>sarawak</code></li> <li><code>selangor</code></li> <li><code>terengganu</code></li> <li><code>kuala-lumpur</code></li> <li><code>labuan</code></li> <li><code>putrajaya</code></li></ul>
        </div>
        </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id-">GET /api/v1/manage/venues/{venue}. The route checks VenuePolicy::view first.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id-">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id-" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id-">PUT /api/v1/manage/venues/{venue}: save the profile (FR-06). Owners only (VenuePolicy::update).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"slug\": \"n\",
    \"state\": \"terengganu\",
    \"description\": \"Animi quos velit et fugiat.\",
    \"address_line\": \"d\",
    \"city\": \"l\",
    \"postcode\": \"56425\",
    \"phone\": \"dljnikhwaykcmyuw\",
    \"facilities\": [
        \"changing_rooms\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "slug": "n",
    "state": "terengganu",
    "description": "Animi quos velit et fugiat.",
    "address_line": "d",
    "city": "l",
    "postcode": "56425",
    "phone": "dljnikhwaykcmyuw",
    "facilities": [
        "changing_rooms"
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id-">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id-" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="b"
               data-component="body">
    <br>
<p>Must be at least 3 characters. Must not be greater than 100 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="slug"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="n"
               data-component="body">
    <br>
<p>Must match the regex /^[a-z0-9]+(?:-[a-z0-9]+)*$/. Must be at least 3 characters. Must not be greater than 60 characters. Example: <code>n</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="state"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="terengganu"
               data-component="body">
    <br>
<p>Example: <code>terengganu</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>johor</code></li> <li><code>kedah</code></li> <li><code>kelantan</code></li> <li><code>melaka</code></li> <li><code>negeri-sembilan</code></li> <li><code>pahang</code></li> <li><code>perak</code></li> <li><code>perlis</code></li> <li><code>pulau-pinang</code></li> <li><code>sabah</code></li> <li><code>sarawak</code></li> <li><code>selangor</code></li> <li><code>terengganu</code></li> <li><code>kuala-lumpur</code></li> <li><code>labuan</code></li> <li><code>putrajaya</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="Animi quos velit et fugiat."
               data-component="body">
    <br>
<p>Must not be greater than 2000 characters. Example: <code>Animi quos velit et fugiat.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address_line</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address_line"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="d"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>d</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="l"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>l</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>postcode</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="postcode"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="56425"
               data-component="body">
    <br>
<p>Must match the regex /^\d{5}$/. Example: <code>56425</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               value="dljnikhwaykcmyuw"
               data-component="body">
    <br>
<p>Must not be greater than 20 characters. Example: <code>dljnikhwaykcmyuw</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>facilities</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="facilities[0]"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               data-component="body">
        <input type="text" style="display: none"
               name="facilities[1]"                data-endpoint="PUTapi-v1-manage-venues--venue_id-"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>parking</code></li> <li><code>surau</code></li> <li><code>toilets</code></li> <li><code>showers</code></li> <li><code>changing_rooms</code></li> <li><code>lockers</code></li> <li><code>canteen</code></li> <li><code>wifi</code></li> <li><code>air_conditioning</code></li> <li><code>equipment_rental</code></li></ul>
        </div>
        </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--settings">PUT /api/v1/manage/venues/{venue}/settings: how players may book (FR-13). Owners only.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--settings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/settings" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"slot_minutes\": 60,
    \"min_minutes\": 1,
    \"max_minutes\": 22,
    \"booking_window_days\": 7,
    \"min_notice_minutes\": 30,
    \"cancel_cutoff_hours\": 24,
    \"refund_note\": \"z\",
    \"booking_emails\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/settings"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "slot_minutes": 60,
    "min_minutes": 1,
    "max_minutes": 22,
    "booking_window_days": 7,
    "min_notice_minutes": 30,
    "cancel_cutoff_hours": 24,
    "refund_note": "z",
    "booking_emails": false
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--settings">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--settings" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/settings"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>slot_minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="slot_minutes"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="60"
               data-component="body">
    <br>
<p>Example: <code>60</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>30</code></li> <li><code>60</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>min_minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="min_minutes"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="1"
               data-component="body">
    <br>
<p>Must be at least 30. Must not be greater than 720. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>max_minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="max_minutes"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="22"
               data-component="body">
    <br>
<p>Must be at least 30. Must not be greater than 720. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>booking_window_days</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="booking_window_days"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="7"
               data-component="body">
    <br>
<p>Must be at least 1. Must not be greater than 90. Example: <code>7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>min_notice_minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="min_notice_minutes"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="30"
               data-component="body">
    <br>
<p>Example: <code>30</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>0</code></li> <li><code>30</code></li> <li><code>60</code></li> <li><code>120</code></li> <li><code>1440</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cancel_cutoff_hours</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="cancel_cutoff_hours"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="24"
               data-component="body">
    <br>
<p>Example: <code>24</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>2</code></li> <li><code>6</code></li> <li><code>12</code></li> <li><code>24</code></li> <li><code>48</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>refund_note</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="refund_note"                data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
               value="z"
               data-component="body">
    <br>
<p>Must not be greater than 300 characters. Example: <code>z</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>booking_emails</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-v1-manage-venues--venue_id--settings" style="display: none">
            <input type="radio" name="booking_emails"
                   value="true"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-v1-manage-venues--venue_id--settings" style="display: none">
            <input type="radio" name="booking_emails"
                   value="false"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--settings"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--hours">GET /api/v1/manage/venues/{venue}/hours</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--hours">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/hours" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/hours"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--hours">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--hours" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--hours"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--hours"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--hours" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--hours">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--hours" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/hours"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--hours', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/hours</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--hours"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--hours"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--hours"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--hours"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--hours">PUT /api/v1/manage/venues/{venue}/hours: replaces the whole week.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--hours">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/hours" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"periods\": [
        {
            \"weekday\": 1,
            \"opens_at\": \"04:30\",
            \"closes_at\": \"04:30\"
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/hours"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "periods": [
        {
            "weekday": 1,
            "opens_at": "04:30",
            "closes_at": "04:30"
        }
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--hours">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--hours" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--hours"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--hours"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--hours" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--hours">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--hours" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/hours"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--hours', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/hours</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>periods</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>weekday</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="periods.0.weekday"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="1"
               data-component="body">
    <br>
<p>Must be between 0 and 6. Example: <code>1</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>opens_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="periods.0.opens_at"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="04:30"
               data-component="body">
    <br>
<p>Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>closes_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="periods.0.closes_at"                data-endpoint="PUTapi-v1-manage-venues--venue_id--hours"
               value="04:30"
               data-component="body">
    <br>
<p>Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--closures">GET /api/v1/manage/venues/{venue}/closures: today&#039;s and future closures, soonest first.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--closures">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/closures" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/closures"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--closures">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--closures" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--closures"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--closures"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--closures" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--closures">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--closures" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/closures"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--closures', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/closures</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--closures"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--closures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--closures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--closures"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--closures">POST /api/v1/manage/venues/{venue}/closures</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--closures">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/closures" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"starts_on\": \"2052-11-27\",
    \"ends_on\": \"2052-11-27\",
    \"reason\": \"n\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/closures"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "starts_on": "2052-11-27",
    "ends_on": "2052-11-27",
    "reason": "n"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--closures">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--closures" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--closures"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--closures"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--closures" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--closures">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--closures" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/closures"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--closures', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/closures</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_on</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_on"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="2052-11-27"
               data-component="body">
    <br>
<p>Must be a valid date in the format <code>Y-m-d</code>. Must be a date after or equal to <code>2026-11-04</code>. Example: <code>2052-11-27</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ends_on</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ends_on"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="2052-11-27"
               data-component="body">
    <br>
<p>Must be a valid date in the format <code>Y-m-d</code>. Must be a date after or equal to <code>starts_on</code>. Example: <code>2052-11-27</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-v1-manage-venues--venue_id--closures"
               value="n"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>n</code></p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">DELETE /api/v1/manage/venues/{venue}/closures/{closure}
The route only finds closures of this venue (scopeBindings), so venue A&#039;s URL
can never remove venue B&#039;s closure.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/closures/16" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/closures/16"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--closures--closure_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/closures/{closure_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--closures--closure_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/closures/{closure_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>closure_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="closure_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--closures--closure_id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the closure. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--courts">GET /api/v1/manage/venues/{venue}/courts: in the owner&#039;s chosen order.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--courts">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/courts" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--courts">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--courts" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--courts"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--courts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--courts" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--courts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--courts" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/courts"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--courts', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/courts</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--courts"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--courts"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--courts">POST /api/v1/manage/venues/{venue}/courts: the new court goes at the end of the list.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--courts">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"is_indoor\": true,
    \"base_price_sen\": 22,
    \"is_active\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "is_indoor": true,
    "base_price_sen": 22,
    "is_active": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--courts">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--courts" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--courts"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--courts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--courts" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--courts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--courts" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/courts"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--courts', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/courts</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 50 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_indoor</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-manage-venues--venue_id--courts" style="display: none">
            <input type="radio" name="is_indoor"
                   value="true"
                   data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-manage-venues--venue_id--courts" style="display: none">
            <input type="radio" name="is_indoor"
                   value="false"
                   data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>base_price_sen</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="base_price_sen"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               value="22"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 100000. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_active</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-manage-venues--venue_id--courts" style="display: none">
            <input type="radio" name="is_active"
                   value="true"
                   data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-manage-venues--venue_id--courts" style="display: none">
            <input type="radio" name="is_active"
                   value="false"
                   data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sports</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sports[0]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               data-component="body">
        <input type="text" style="display: none"
               name="sports[1]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--courts"
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts-order">PUT /api/v1/manage/venues/{venue}/courts/order  { &quot;court_ids&quot;: [7, 4, 5] }
Every court of the venue, each exactly once, in the new order.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--courts-order">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/order" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"court_ids\": [
        16
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/order"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "court_ids": [
        16
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--courts-order">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--courts-order" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--courts-order"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--courts-order"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--courts-order" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--courts-order">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--courts-order" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/courts/order"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--courts-order', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/courts/order</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_ids[0]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               data-component="body">
        <input type="number" style="display: none"
               name="court_ids[1]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts-order"
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--courts--court_id-">PUT /api/v1/manage/venues/{venue}/courts/{court}: name, indoor, active and sports.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--courts--court_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/5" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"is_indoor\": true,
    \"base_price_sen\": 22,
    \"is_active\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/5"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "is_indoor": true,
    "base_price_sen": 22,
    "is_active": false
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--courts--court_id-">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--courts--court_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--courts--court_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--courts--court_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--courts--court_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--courts--court_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--courts--court_id-" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/courts/{court_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--courts--court_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/courts/{court_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="5"
               data-component="url">
    <br>
<p>The ID of the court. Example: <code>5</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 50 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_indoor</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-" style="display: none">
            <input type="radio" name="is_indoor"
                   value="true"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-" style="display: none">
            <input type="radio" name="is_indoor"
                   value="false"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>base_price_sen</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="base_price_sen"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               value="22"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 100000. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_active</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-" style="display: none">
            <input type="radio" name="is_active"
                   value="true"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-" style="display: none">
            <input type="radio" name="is_active"
                   value="false"
                   data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sports</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sports[0]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               data-component="body">
        <input type="text" style="display: none"
               name="sports[1]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--courts--court_id-"
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">DELETE /api/v1/manage/venues/{venue}/courts/{court}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/5" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/courts/5"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--courts--court_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--courts--court_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--courts--court_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--courts--court_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--courts--court_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/courts/{court_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--courts--court_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/courts/{court_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--courts--court_id-"
               value="5"
               data-component="url">
    <br>
<p>The ID of the court. Example: <code>5</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--price-rules">GET /api/v1/manage/venues/{venue}/price-rules: all-courts rules first, then by start time.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--price-rules">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--price-rules">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--price-rules" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--price-rules"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--price-rules"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--price-rules" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--price-rules">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--price-rules" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/price-rules"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--price-rules', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/price-rules</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--price-rules"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--price-rules"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--price-rules"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--price-rules"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--price-rules">POST /api/v1/manage/venues/{venue}/price-rules</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--price-rules">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"court_id\": 16,
    \"weekdays\": [
        1
    ],
    \"starts_at\": \"04:30\",
    \"ends_at\": \"04:30\",
    \"price_sen\": 8
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "court_id": 16,
    "weekdays": [
        1
    ],
    "starts_at": "04:30",
    "ends_at": "04:30",
    "price_sen": 8
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--price-rules">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--price-rules" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--price-rules"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--price-rules"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--price-rules" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--price-rules">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--price-rules" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/price-rules"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--price-rules', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/price-rules</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 50 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="16"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>weekdays</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="weekdays[0]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               data-component="body">
        <input type="number" style="display: none"
               name="weekdays[1]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               data-component="body">
    <br>
<p>Must be between 0 and 6.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="04:30"
               data-component="body">
    <br>
<p>Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ends_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ends_at"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="04:30"
               data-component="body">
    <br>
<p>The value and <code>starts_at</code> must be different. Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>price_sen</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="price_sen"                data-endpoint="POSTapi-v1-manage-venues--venue_id--price-rules"
               value="8"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 100000. Example: <code>8</code></p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">PUT /api/v1/manage/venues/{venue}/price-rules/{priceRule}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules/3" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"b\",
    \"court_id\": 16,
    \"weekdays\": [
        1
    ],
    \"starts_at\": \"04:30\",
    \"ends_at\": \"04:30\",
    \"price_sen\": 8
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules/3"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "b",
    "court_id": 16,
    "weekdays": [
        1
    ],
    "starts_at": "04:30",
    "ends_at": "04:30",
    "price_sen": 8
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/price-rules/{priceRule_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/price-rules/{priceRule_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>priceRule_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="priceRule_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="3"
               data-component="url">
    <br>
<p>The ID of the priceRule. Example: <code>3</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 50 characters. Example: <code>b</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="16"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>weekdays</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="weekdays[0]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               data-component="body">
        <input type="number" style="display: none"
               name="weekdays[1]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               data-component="body">
    <br>
<p>Must be between 0 and 6.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="04:30"
               data-component="body">
    <br>
<p>Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ends_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ends_at"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="04:30"
               data-component="body">
    <br>
<p>The value and <code>starts_at</code> must be different. Must match the regex /^([01]\d|2[0-3]):(00|30)$/. Example: <code>04:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>price_sen</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="price_sen"                data-endpoint="PUTapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="8"
               data-component="body">
    <br>
<p>Must be at least 0. Must not be greater than 100000. Example: <code>8</code></p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">DELETE /api/v1/manage/venues/{venue}/price-rules/{priceRule}: those hours go back to the normal price.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules/3" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/price-rules/3"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/price-rules/{priceRule_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/price-rules/{priceRule_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>priceRule_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="priceRule_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--price-rules--priceRule_id-"
               value="3"
               data-component="url">
    <br>
<p>The ID of the priceRule. Example: <code>3</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--staff">GET /api/v1/manage/venues/{venue}/staff</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--staff">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/staff" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--staff">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--staff" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--staff"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--staff"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--staff" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--staff">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--staff" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/staff"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--staff', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/staff</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--staff"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--staff"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--staff"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--staff"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--staff-invites">POST /api/v1/manage/venues/{venue}/staff-invites  { &quot;email&quot;: &quot;aina@example.com&quot; }
Inviting the same email again sends a fresh link; the old one stops working.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--staff-invites">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff-invites" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff-invites"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--staff-invites">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--staff-invites" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--staff-invites"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--staff-invites"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--staff-invites" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--staff-invites">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--staff-invites" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/staff-invites"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--staff-invites', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/staff-invites</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--staff-invites"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--staff-invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--staff-invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--staff-invites"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">DELETE /api/v1/manage/venues/{venue}/staff-invites/{invite}: the link stops working.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff-invites/16" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff-invites/16"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/staff-invites/{invite_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/staff-invites/{invite_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>invite_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="invite_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff-invites--invite_id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the invite. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--staff--user-">DELETE /api/v1/manage/venues/{venue}/staff/{user}: they lose access straight away.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--staff--user-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff/564" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/staff/564"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--staff--user-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--staff--user-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--staff--user-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--staff--user-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--staff--user-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--staff--user-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--staff--user-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/staff/{user}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--staff--user-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/staff/{user}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff--user-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff--user-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff--user-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff--user-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--staff--user-"
               value="564"
               data-component="url">
    <br>
<p>Example: <code>564</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-GETapi-v1-manage-venues--venue_id--photos">GET /api/v1/manage/venues/{venue}/photos: in the owner&#039;s chosen order.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--photos">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/photos" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--photos">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--photos" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--photos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--photos"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--photos" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--photos">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--photos" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/photos"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--photos', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/photos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--photos"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--photos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--photos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--photos"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="venue-setup-owners-POSTapi-v1-manage-venues--venue_id--photos">POST /api/v1/manage/venues/{venue}/photos: the new photo goes at the end.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--photos">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "photo=@C:\Users\User\AppData\Local\Temp\php8B0E.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('photo', document.querySelector('input[name="photo"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--photos">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--photos" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--photos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--photos"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--photos" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--photos">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--photos" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/photos"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--photos', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/photos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--photos"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--photos"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--photos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--photos"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>photo</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="photo"                data-endpoint="POSTapi-v1-manage-venues--venue_id--photos"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must be an image. Must not be greater than 4096 kilobytes. Example: <code>C:\Users\User\AppData\Local\Temp\php8B0E.tmp</code></p>
        </div>
        </form>

                    <h2 id="venue-setup-owners-PUTapi-v1-manage-venues--venue_id--photos-order">PUT /api/v1/manage/venues/{venue}/photos/order  { &quot;photo_ids&quot;: [5, 2, 3] }</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-PUTapi-v1-manage-venues--venue_id--photos-order">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos/order" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"photo_ids\": [
        16
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos/order"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "photo_ids": [
        16
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-manage-venues--venue_id--photos-order">
</span>
<span id="execution-results-PUTapi-v1-manage-venues--venue_id--photos-order" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-manage-venues--venue_id--photos-order"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-manage-venues--venue_id--photos-order"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-manage-venues--venue_id--photos-order" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-manage-venues--venue_id--photos-order">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-manage-venues--venue_id--photos-order" data-method="PUT"
      data-path="api/v1/manage/venues/{venue_id}/photos/order"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-manage-venues--venue_id--photos-order', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/manage/venues/{venue_id}/photos/order</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>photo_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_ids[0]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               data-component="body">
        <input type="number" style="display: none"
               name="photo_ids[1]"                data-endpoint="PUTapi-v1-manage-venues--venue_id--photos-order"
               data-component="body">
    <br>

        </div>
        </form>

                    <h2 id="venue-setup-owners-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">DELETE /api/v1/manage/venues/{venue}/photos/{photo}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos/2" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/photos/2"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--photos--photo_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/photos/{photo_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--photos--photo_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/photos/{photo_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>photo_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--photos--photo_id-"
               value="2"
               data-component="url">
    <br>
<p>The ID of the photo. Example: <code>2</code></p>
            </div>
                    </form>

                <h1 id="day-schedule-staff">Day schedule (staff)</h1>

    

                                <h2 id="day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule">GET /api/v1/manage/venues/{venue}/schedule?date=2026-11-06 (today in Malaysia when left out)</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>times: the day's half hours, from opening to closing, after midnight too (ADR-015), plus any
half hour a booking or block holds outside today's hours. courts: the active courts, and any
other court with a booking or block that day. bookings: everything except cancelled and expired
ones (holds still waiting for payment included).
blocks: times closed for repairs or a private event (FR-21).</p>

<span id="example-requests-GETapi-v1-manage-venues--venue_id--schedule">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/schedule" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/schedule"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--schedule">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--schedule" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--schedule"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--schedule"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--schedule" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--schedule">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--schedule" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/schedule"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--schedule', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/schedule</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--schedule"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="day-schedule-staff-GETapi-v1-manage-venues--venue_id--schedule-version">GET /api/v1/manage/venues/{venue}/schedule/version (FR-14)</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>A tiny answer the Schedule tab asks for every 10 seconds: a word that changes whenever a booking,
cancel, block or setting at this venue is saved (AvailabilityCache, ADR-028). The page reloads the
whole schedule only when it changes, so the counter sees new online bookings within seconds.</p>

<span id="example-requests-GETapi-v1-manage-venues--venue_id--schedule-version">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/schedule/version" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/schedule/version"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--schedule-version">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--schedule-version" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--schedule-version"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--schedule-version"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--schedule-version" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--schedule-version">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--schedule-version" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/schedule/version"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--schedule-version', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/schedule/version</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--schedule-version"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule-version"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule-version"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--schedule-version"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings">POST /api/v1/manage/venues/{venue}/bookings</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--bookings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"court_id\": 16,
    \"sport\": \"architecto\",
    \"starts_at\": \"2026-11-04T15:46:09\",
    \"minutes\": 22,
    \"source\": \"walk_in\",
    \"customer_name\": \"g\",
    \"customer_phone\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "court_id": 16,
    "sport": "architecto",
    "starts_at": "2026-11-04T15:46:09",
    "minutes": 22,
    "source": "walk_in",
    "customer_name": "g",
    "customer_phone": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--bookings">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--bookings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--bookings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--bookings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--bookings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--bookings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--bookings" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/bookings"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--bookings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/bookings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="16"
               data-component="body">
    <br>
<p>Example: <code>16</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sport</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sport"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="architecto"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="2026-11-04T15:46:09"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-11-04T15:46:09</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="minutes"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="22"
               data-component="body">
    <br>
<p>Must be at least 30. Must not be greater than 720. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>source</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="source"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="walk_in"
               data-component="body">
    <br>
<p>Example: <code>walk_in</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>walk_in</code></li> <li><code>phone</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>customer_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="customer_name"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>g</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>customer_phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="customer_phone"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--blocks">POST /api/v1/manage/venues/{venue}/blocks
{ &quot;court_ids&quot;: [6, 7], &quot;starts_at&quot;: &quot;2026-11-06T18:00:00+08:00&quot;, &quot;minutes&quot;: 120, &quot;reason&quot;: &quot;Floor repair&quot; }
One block per court, all or nothing: 409 SLOT_TAKEN (with the bookings in the way) when any
of these half hours is already booked or blocked.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-manage-venues--venue_id--blocks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/blocks" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"court_ids\": [
        16
    ],
    \"starts_at\": \"2026-11-04T15:46:09\",
    \"minutes\": 22,
    \"reason\": \"g\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/blocks"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "court_ids": [
        16
    ],
    "starts_at": "2026-11-04T15:46:09",
    "minutes": 22,
    "reason": "g"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--blocks">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--blocks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--blocks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--blocks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--blocks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--blocks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--blocks" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/blocks"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--blocks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/blocks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>court_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="court_ids[0]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               data-component="body">
        <input type="number" style="display: none"
               name="court_ids[1]"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               data-component="body">
    <br>

        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="2026-11-04T15:46:09"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-11-04T15:46:09</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>minutes</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="minutes"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="22"
               data-component="body">
    <br>
<p>Half-hour steps, at most a whole day; longer closures are closure days (FR-07). Must be at least 30. Must not be greater than 1440. Example: <code>22</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-v1-manage-venues--venue_id--blocks"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 100 characters. Example: <code>g</code></p>
        </div>
        </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">POST .</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>../bookings/{code}/check-in: the player arrived. From an hour before the start until it ends.</p>

<span id="example-requests-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/check-in" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/check-in"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/bookings/{booking_code}/check-in"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/bookings/{booking_code}/check-in</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>booking_code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="booking_code"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--check-in"
               value="31"
               data-component="url">
    <br>
<p>Example: <code>31</code></p>
            </div>
                    </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">POST .</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>../bookings/{code}/no-show: the player didn't come. The time stays taken, as history.</p>

<span id="example-requests-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/no-show" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/no-show"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/bookings/{booking_code}/no-show"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/bookings/{booking_code}/no-show</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>booking_code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="booking_code"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--no-show"
               value="31"
               data-component="url">
    <br>
<p>Example: <code>31</code></p>
            </div>
                    </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">POST .</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>../bookings/{code}/paid  { "method": "cash" | "qr" | "card" }</p>

<span id="example-requests-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/paid" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"method\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/paid"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "method": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/bookings/{booking_code}/paid"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/bookings/{booking_code}/paid</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>booking_code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="booking_code"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="31"
               data-component="url">
    <br>
<p>Example: <code>31</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>method</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="method"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--paid"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="day-schedule-staff-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">POST .</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>../bookings/{code}/cancel  { "reason": "Rain" }
Staff and owners can cancel any confirmed booking, or one waiting for payment, with no cutoff
(unlike players, FR-24).
The time is free for others straight away.</p>

<span id="example-requests-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/cancel" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"reason\": \"b\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/bookings/31/cancel"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "reason": "b"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">
</span>
<span id="execution-results-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel" data-method="POST"
      data-path="api/v1/manage/venues/{venue_id}/bookings/{booking_code}/cancel"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/manage/venues/{venue_id}/bookings/{booking_code}/cancel</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>booking_code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="booking_code"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="31"
               data-component="url">
    <br>
<p>Example: <code>31</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-v1-manage-venues--venue_id--bookings--booking_code--cancel"
               value="b"
               data-component="body">
    <br>
<p>Must not be greater than 200 characters. Example: <code>b</code></p>
        </div>
        </form>

                    <h2 id="day-schedule-staff-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">DELETE /api/v1/manage/venues/{venue}/blocks/{slotBlock}: the time can be booked again.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://courtku.vercel.app/api/v1/manage/venues/1/blocks/16" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/blocks/16"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">
</span>
<span id="execution-results-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-" data-method="DELETE"
      data-path="api/v1/manage/venues/{venue_id}/blocks/{slotBlock_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/v1/manage/venues/{venue_id}/blocks/{slotBlock_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>slotBlock_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="slotBlock_id"                data-endpoint="DELETEapi-v1-manage-venues--venue_id--blocks--slotBlock_id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the slotBlock. Example: <code>16</code></p>
            </div>
                    </form>

                <h1 id="staff-invites">Staff invites</h1>

    

                                <h2 id="staff-invites-GETapi-v1-invites--token-">GET /api/v1/invites/{token}: what the invite is, before accepting.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-invites--token-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/invites/5f2b8c1e9a7d4f6b" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/invites/5f2b8c1e9a7d4f6b"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-invites--token-">
    </span>
<span id="execution-results-GETapi-v1-invites--token-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-invites--token-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-invites--token-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-invites--token-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-invites--token-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-invites--token-" data-method="GET"
      data-path="api/v1/invites/{token}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invites--token-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/invites/{token}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-invites--token-"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-invites--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-invites--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="GETapi-v1-invites--token-"
               value="5f2b8c1e9a7d4f6b"
               data-component="url">
    <br>
<p>The code from the invite email. Example: <code>5f2b8c1e9a7d4f6b</code></p>
            </div>
                    </form>

                    <h2 id="staff-invites-POSTapi-v1-invites--token--accept">POST /api/v1/invites/{token}/accept: the logged-in, verified user joins the venue.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-invites--token--accept">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/invites/5f2b8c1e9a7d4f6b/accept" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/invites/5f2b8c1e9a7d4f6b/accept"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-invites--token--accept">
</span>
<span id="execution-results-POSTapi-v1-invites--token--accept" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-invites--token--accept"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-invites--token--accept"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-invites--token--accept" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-invites--token--accept">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-invites--token--accept" data-method="POST"
      data-path="api/v1/invites/{token}/accept"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invites--token--accept', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/invites/{token}/accept</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-invites--token--accept"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-v1-invites--token--accept"
               value="5f2b8c1e9a7d4f6b"
               data-component="url">
    <br>
<p>The code from the invite email. Example: <code>5f2b8c1e9a7d4f6b</code></p>
            </div>
                    </form>

                <h1 id="reports-owners">Reports (owners)</h1>

    

                                <h2 id="reports-owners-GETapi-v1-manage-venues--venue_id--reports">GET /api/v1/manage/venues/{venue}/reports?from=2026-11-01&amp;to=2026-11-30</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--reports">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/reports" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/reports"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--reports">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--reports" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--reports"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--reports"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--reports" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--reports">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--reports" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/reports"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--reports', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/reports</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--reports"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="reports-owners-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">GET /api/v1/manage/venues/{venue}/reports/bookings.csv?from=2026-11-01&amp;to=2026-11-30</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/manage/venues/1/reports/bookings.csv" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/manage/venues/1/reports/bookings.csv"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">
    </span>
<span id="execution-results-GETapi-v1-manage-venues--venue_id--reports-bookings-csv" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-manage-venues--venue_id--reports-bookings-csv"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-manage-venues--venue_id--reports-bookings-csv"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-manage-venues--venue_id--reports-bookings-csv" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-manage-venues--venue_id--reports-bookings-csv">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-manage-venues--venue_id--reports-bookings-csv" data-method="GET"
      data-path="api/v1/manage/venues/{venue_id}/reports/bookings.csv"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-manage-venues--venue_id--reports-bookings-csv', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/manage/venues/{venue_id}/reports/bookings.csv</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-manage-venues--venue_id--reports-bookings-csv"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports-bookings-csv"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports-bookings-csv"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="GETapi-v1-manage-venues--venue_id--reports-bookings-csv"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="admin">Admin</h1>

    

                                <h2 id="admin-GETapi-v1-admin-venues">GET /api/v1/admin/venues?status=pending: newest first, 25 per page.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-admin-venues">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/admin/venues" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"suspended\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/admin/venues"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "suspended"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-admin-venues">
    </span>
<span id="execution-results-GETapi-v1-admin-venues" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-admin-venues"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-admin-venues"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-admin-venues" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-admin-venues">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-admin-venues" data-method="GET"
      data-path="api/v1/admin/venues"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-admin-venues', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/admin/venues</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-admin-venues"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-admin-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-admin-venues"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="GETapi-v1-admin-venues"
               value="suspended"
               data-component="body">
    <br>
<p>Example: <code>suspended</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>pending</code></li> <li><code>active</code></li> <li><code>suspended</code></li></ul>
        </div>
        </form>

                    <h2 id="admin-POSTapi-v1-admin-venues--venue_id--approve">POST /api/v1/admin/venues/{venue}/approve</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-admin-venues--venue_id--approve">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/admin/venues/1/approve" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/admin/venues/1/approve"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-admin-venues--venue_id--approve">
</span>
<span id="execution-results-POSTapi-v1-admin-venues--venue_id--approve" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-admin-venues--venue_id--approve"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-admin-venues--venue_id--approve"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-admin-venues--venue_id--approve" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-admin-venues--venue_id--approve">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-admin-venues--venue_id--approve" data-method="POST"
      data-path="api/v1/admin/venues/{venue_id}/approve"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-admin-venues--venue_id--approve', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/admin/venues/{venue_id}/approve</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-admin-venues--venue_id--approve"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-admin-venues--venue_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-admin-venues--venue_id--approve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-admin-venues--venue_id--approve"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="admin-POSTapi-v1-admin-venues--venue_id--suspend">POST /api/v1/admin/venues/{venue}/suspend</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-v1-admin-venues--venue_id--suspend">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://courtku.vercel.app/api/v1/admin/venues/1/suspend" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/admin/venues/1/suspend"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-admin-venues--venue_id--suspend">
</span>
<span id="execution-results-POSTapi-v1-admin-venues--venue_id--suspend" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-admin-venues--venue_id--suspend"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-admin-venues--venue_id--suspend"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-admin-venues--venue_id--suspend" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-admin-venues--venue_id--suspend">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-admin-venues--venue_id--suspend" data-method="POST"
      data-path="api/v1/admin/venues/{venue_id}/suspend"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-admin-venues--venue_id--suspend', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/admin/venues/{venue_id}/suspend</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="POSTapi-v1-admin-venues--venue_id--suspend"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-admin-venues--venue_id--suspend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-admin-venues--venue_id--suspend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>venue_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="venue_id"                data-endpoint="POSTapi-v1-admin-venues--venue_id--suspend"
               value="1"
               data-component="url">
    <br>
<p>The ID of the venue. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="admin-GETapi-v1-admin-stats">GET api/v1/admin/stats</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-admin-stats">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/v1/admin/stats" \
    --header "X-XSRF-TOKEN: {XSRF-TOKEN cookie value}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/v1/admin/stats"
);

const headers = {
    "X-XSRF-TOKEN": "{XSRF-TOKEN cookie value}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-admin-stats">
    </span>
<span id="execution-results-GETapi-v1-admin-stats" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-admin-stats"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-admin-stats"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-admin-stats" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-admin-stats">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-admin-stats" data-method="GET"
      data-path="api/v1/admin/stats"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-admin-stats', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/admin/stats</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-XSRF-TOKEN</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-XSRF-TOKEN" class="auth-value"               data-endpoint="GETapi-v1-admin-stats"
               value="{XSRF-TOKEN cookie value}"
               data-component="header">
    <br>
<p>Example: <code>{XSRF-TOKEN cookie value}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-admin-stats"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-admin-stats"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="health">Health</h1>

    

                                <h2 id="health-GETapi-health">GET api/health</h2>

<p>
</p>



<span id="example-requests-GETapi-health">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://courtku.vercel.app/api/health" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://courtku.vercel.app/api/health"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-health">
    </span>
<span id="execution-results-GETapi-health" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-health"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-health"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-health" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-health">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-health" data-method="GET"
      data-path="api/health"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-health', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/health</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
