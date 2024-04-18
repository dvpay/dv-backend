<?php

return [
    'default' => 'huobi',
    'driver' => [
        'huobi' => [
            'apiUrl' => 'https://api.huobi.pro',
        ]
    ],
    //legacy
    'huobi'                          => [
        'apiUrl' => 'https://api.huobi.pro',
    ],
    'withdrawalAmountCorrectionUsdt' => env('WITHDRAWAL_AMOUNT_CORRECTION_USDT', 50),


];