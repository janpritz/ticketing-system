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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'faq_updater' => [
        'url' => env('FAQ_UPDATER_URL'),
        'batch_url' => env('FAQ_UPDATER_BATCH_URL', env('FAQ_UPDATER_URL')),
        'secret' => env('FAQ_UPDATER_SECRET'),
    ],

    'faq_deleter' => [
        'url' => env('FAQ_DELETER_URL'),
        'secret' => env('FAQ_DELETER_SECRET'),
    ],

    'faq_refetch' => [
        'url' => env('FAQ_REFETCH_URL'),
        'secret' => env('FAQ_REFETCH_SECRET'),
    ],

    'faq_sync' => [
        'url' => env('FAQ_SYNC_URL'),
        'secret' => env('FAQ_UPDATER_SECRET'),
    ],

];
