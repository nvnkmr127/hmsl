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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mappls' => [
        'client_id' => env('MAPPLS_CLIENT_ID'),
        'client_secret' => env('MAPPLS_CLIENT_SECRET'),
        'autosuggest_bounds_filter' => env('MAPPLS_AUTOSUGGEST_BOUNDS_FILTER', 'bounds: 19.95,77.15; 15.80,81.05'),
        'autosuggest_tokenize_address' => (bool) env('MAPPLS_AUTOSUGGEST_TOKENIZE_ADDRESS', false),
        'autosuggest_max_query_length' => (int) env('MAPPLS_AUTOSUGGEST_MAX_QUERY_LENGTH', 45),
    ],

];
