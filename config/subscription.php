<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency used for subscriptions and billing.
    |
    */
    'currency' => env('SUBSCRIPTION_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Currency Symbol
    |--------------------------------------------------------------------------
    |
    | The symbol for the currency.
    |
    */
    'currency_symbol' => env('SUBSCRIPTION_CURRENCY_SYMBOL', '$'),

    /*
    |--------------------------------------------------------------------------
    | Trial Period Days
    |--------------------------------------------------------------------------
    |
    | Default number of trial days for new subscriptions.
    |
    */
    'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Grace Period Days
    |--------------------------------------------------------------------------
    |
    | Number of days to allow for late payments before subscription expires.
    |
    */
    'grace_period_days' => (int) env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Invoice Due Days
    |--------------------------------------------------------------------------
    |
    | Default number of days until an invoice is due.
    |
    */
    'invoice_due_days' => (int) env('SUBSCRIPTION_INVOICE_DUE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Supported payment gateways for processing subscription payments.
    |
    */
    'payment_gateways' => [
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'paddle' => [
            'enabled' => env('PADDLE_ENABLED', false),
            'vendor_id' => env('PADDLE_VENDOR_ID'),
            'vendor_auth_code' => env('PADDLE_VENDOR_AUTH_CODE'),
            'public_key' => env('PADDLE_PUBLIC_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific subscription features.
    |
    */
    'features' => [
        'allow_plan_changes' => true,
        'allow_cancellations' => true,
        'allow_resumptions' => true,
        'proration' => true,
        'refunds' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure subscription-related notifications.
    |
    */
    'notifications' => [
        'renewal_reminder_days' => [7, 3, 1], // Days before renewal to send reminder
        'trial_ending_reminder_days' => [3, 1], // Days before trial ends to send reminder
        'payment_failed_retry_days' => [1, 3, 5], // Days to retry failed payments
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | Configure invoice generation settings.
    |
    */
    'invoice' => [
        'prefix' => 'SUB',
        'auto_generate' => true,
        'tax_rate' => (float) env('SUBSCRIPTION_TAX_RATE', 0),
    ],
];
