<?php

return [

    /*
     * API endpoint settings.
     *
     */
    'api' => [
        'endpoint' => 'api.mailgun.net',
        'version' => 'v3',
        'ssl' => true
    ],

    /*
     * Domain name registered with Mailgun
     *
     */
    'domain' => env('MAILGUN_DOMAIN'),

    /*
     * Mailgun (private) API key
     *
     */
    'api_key' => env('MAILGUN_API_KEY'),

    /*
     * Mailgun public API key
     *
     */
    'public_api_key' => env('MAILGUN_API_PUBLIC_KEY'),

    /*
     * Exclude events that were done in test mode
     *
     */
    'exclude_test_mode_events' => true
];
