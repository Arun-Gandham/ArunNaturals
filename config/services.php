<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'delhivery' => [
        'base_url'    => env('DELHIVERY_BASE_URL', 'https://staging-express.delhivery.com'),
        'token'       => env('DELHIVERY_TOKEN'),
        'timeout'     => (int) env('DELHIVERY_TIMEOUT', 30),
        'retry_times' => (int) env('DELHIVERY_RETRY_TIMES', 3),
        'retry_sleep' => (int) env('DELHIVERY_RETRY_SLEEP', 500),
        'origin_pin'  => env('DELHIVERY_ORIGIN_PIN'),
        'pickup_location' => env('DELHIVERY_PICKUP_LOCATION', 'Arun Natural Products'),
    ],

    'whatsapp' => [
        // Example: 91XXXXXXXXXX (country code + number, no + or spaces)
        'phone' => env('WHATSAPP_PHONE'),
    ],
];
