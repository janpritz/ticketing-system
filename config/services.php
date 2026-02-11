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
        'secret' => env('RASA_SECRET'),
    ],

    'faq_deleter' => [
        'url' => env('FAQ_DELETER_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'faq_refetch' => [
        'url' => env('FAQ_REFETCH_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'faq_sync' => [
        'url' => env('FAQ_SYNC_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'faq_list_docs' => [
        'url' => env('FAQ_LIST_DOCS_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'faq_train_rasa' => [
        'url' => env('FAQ_TRAIN_RASA_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'faq_start_rasa_api' => [
        'url' => env('FAQ_START_RASA_API_URL'),
        'secret' => env('RASA_SECRET'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

];
