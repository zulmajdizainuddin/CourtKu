<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Venue photos (ADR-018). The folder keeps local test photos apart from the live site's.
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'folder' => env('CLOUDINARY_FOLDER', 'courtku'),
    ],

    // Stripe test mode (ADR-023). Each venue connects its own Stripe account (ADR-009); this key is only
    // for trying the gateway locally (php artisan courtku:stripe-try) and connecting the local demo venue.
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    // The every-minute jobs (ADR-022): a cron service calls POST /api/tick with this secret.
    // Empty = the endpoint is off (404); the scheduler can still run `php artisan courtku:tick`.
    'cron' => [
        'secret' => env('CRON_SECRET'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
