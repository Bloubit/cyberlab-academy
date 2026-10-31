<?php

return [
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

    'click' => [
        'merchant_id' => env('CLICK_MERCHANT_ID', ''),
        'service_id' => env('CLICK_SERVICE_ID', ''),
        'secret_key' => env('CLICK_SECRET_KEY', ''),
        'merchant_user_id' => env('CLICK_MERCHANT_USER_ID', ''),
        'base_url' => env('CLICK_BASE_URL', 'https://api.click.uz/v2/merchant'),
    ],

    'payze' => [
        'api_key' => env('PAYZE_API_KEY', ''),
        'api_secret' => env('PAYZE_API_SECRET', ''),
        'base_url' => env('PAYZE_BASE_URL', 'https://api.payze.io/v1'),
    ],
];
