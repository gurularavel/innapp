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

    'kapital' => [
        'base_url' => env('KAPITAL_BASE_URL', 'https://txpgtst.kapitalbank.az/api'),
        'username' => env('KAPITAL_USERNAME'),
        'password' => env('KAPITAL_PASSWORD'),
    ],

    'sms' => [
        'driver'     => env('SMS_DRIVER', 'log'),
        'api_url'    => env('SMS_API_URL'),
        'public_key' => env('SMS_PUBLIC_KEY'),
        'private_key'=> env('SMS_PRIVATE_KEY'),
        'originator' => env('SMS_ORIGINATOR'),
    ],

    /*
     | WhatsApp Cloud API — fallback values. The admin panel
     | (Ayarlar » WhatsApp) writes into the `settings` table and takes priority.
     */
    'whatsapp' => [
        'api_version'     => env('WHATSAPP_API_VERSION', 'v21.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'    => env('WHATSAPP_ACCESS_TOKEN'),
        'language_code'   => env('WHATSAPP_LANGUAGE_CODE', 'az'),
    ],

    /*
     | Google Analytics 4 — measurement id for the gtag.js snippet rendered by
     | resources/views/layouts/_analytics.blade.php. Leave empty to disable.
     */
    'google_analytics' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_ID', 'G-X8ZGYKVJ4V'),
    ],

    // Platform support contact shown by the floating WhatsApp button.
    'support' => [
        'whatsapp' => env('SUPPORT_WHATSAPP', '994557038008'),
    ],

    /*
     | Cloudflare Turnstile — fallback values. The admin panel
     | (Ayarlar » Təhlükəsizlik) writes into the `settings` table and takes
     | priority.
     */
    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

];
