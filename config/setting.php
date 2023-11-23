<?php

declare(strict_types=1);

return [
    'salt' => env('SALT', ''),

    'cache_lifetime' => env('CACHE_LIFETIME', 60),

    'invoice_lifetime' => env('INVOICE_LIFETIME', 1),

    'payment_form_url' => env('PAYMENT_FORM_URL', ''),

    'disabled_blockchains' => explode(',', env('DISABLED_BLOCKCHAINS', '')),

    'rate_source_fake' => env('RATE_SOURCE_FAKE', false),

    'repeat_job_timeout' => env('REPEAT_JOB_TIMEOUT', 10),

    'default_scale' => env('DEFAULT_SCALE', 8),

    'webhook_timeout' => env('WEBHOOK_TIMEOUT', 50),

    'rate_scale' => env('RATE_SCALE', 1),

    'max_rate_difference' => env('MAX_RATE_DIFFERENCE', 10),

    'new_send_webhook_logic' => env('NEW_SEND_WEBHOOK_LOGIC', false),

    'exchange_rate_time' => env('EXCHANGE_RATE_TIME', 10),

    'store_address_hold_time' => 360
];