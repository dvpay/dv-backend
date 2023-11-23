<?php

declare(strict_types=1);

return [
    'fake' => env('PROCESSING_FAKE', false),
    'url' => env('PROCESSING_URL', ''),
    'client' => [
        'id' => env('PROCESSING_CLIENT_ID', ''),
        'key' => env('PROCESSING_CLIENT_KEY', ''),
        'webhookKey' => env('PROCESSING_WEBHOOK_KEY', ''),
    ],
    'multipliers' => [
        'tron' => env('PROCESSING_TRON_WATCH_MULTIPLIER', 1),
        'bitcoin' => env('PROCESSING_BITCOIN_WATCH_MULTIPLIER', 1),
    ],
    'min_transaction_confirmations' => env('MIN_TRANSACTION_CONFIRMATIONS', 6),
    'btc_explorer' => env('BTC_EXPLORER', 'http://localhost:8091')
];