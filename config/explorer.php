<?php

declare(strict_types=1);

return [

    'public' => [
        'ethereum' => [
            'apiKey' => env('ETHERSCAN_SCAN_API_KEY', '')
        ]
    ],
    'proxy' => env('PROXY', ''),
];
