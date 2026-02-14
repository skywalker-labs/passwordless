<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | The number of digits in the OTP.
    |
    */
    'length' => 6,

    /*
    |--------------------------------------------------------------------------
    | OTP Expiry Time
    |--------------------------------------------------------------------------
    |
    | The time in minutes for which the OTP is valid.
    |
    */
    'expiry' => 10, // minutes

    /*
    |--------------------------------------------------------------------------
    | OTP Storage Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "cache", "database"
    |
    */
    'driver' => 'cache',

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | The default channel to send OTPs.
    | Supported: "mail", "log", "sms" (requires implementation)
    |
    */
    'channel' => 'mail', // Default channel: 'mail', 'log', 'sms', 'slack'

    /*
    |--------------------------------------------------------------------------
    | Notification Services
    |--------------------------------------------------------------------------
    |
    | Credentials for external services.
    |
    */
    'services' => [
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'slack' => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
    ],
];
