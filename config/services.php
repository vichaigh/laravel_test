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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Providers
    |--------------------------------------------------------------------------
    |
    | Credentials for the multi-provider payment integration. Each key here
    | matches a driver registered in App\Factories\PaymentProviderFactory.
    |
    */

    'provider1' => [
        'base_url' => env('PROVIDER1_BASE_URL', 'https://api.provider1.com/v1'),
        'api_key' => env('PROVIDER1_API_KEY'),
        'secret' => env('PROVIDER1_API_SECRET'),
        'webhook_secret' => env('PROVIDER1_WEBHOOK_SECRET'),
    ],

    'provider2' => [
        'base_url' => env('PROVIDER2_BASE_URL', 'https://api.provider2.com/v2'),
        'api_key' => env('PROVIDER2_API_KEY'),
        'secret' => env('PROVIDER2_API_SECRET'),
        'webhook_secret' => env('PROVIDER2_WEBHOOK_SECRET'),
    ],

];
