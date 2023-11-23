<?php

return [
    'core' => [
        'minPhpVersion' => '8.1.0'
    ],
    'requirements' => [
        'php' => [
            "openssl",
            "pdo",
            "pdo_mysql",
            "mbstring",
            "exif",
            "tokenizer",
            "JSON",
            "cURL",
            "bcmath",
            "sockets",
            "gmp",
            "fileinfo",
            "XML",
            "ctype"
        ],
        'apache' => [
            'mod_rewrite',
        ],
    ],
    'permissions' => [
        'storage/framework/'     => '775',
        'storage/logs/'          => '775',
        'bootstrap/cache/'       => '775',
        'config/'                => '775'
    ],
    'finish' => env('FINISH_INSTALL', false),
];
