<?php

return [
    'queue' => 'store-webhook',
    'queue-retry' => 'store-webhook-retry',
    'connection' => null,
    'http_verb' => 'post',
    'signature_header_name' => 'X-Sign',
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'timeout_in_seconds' => 10,
    'tries' => 30,
];
