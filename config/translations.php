<?php

// Сервис работает по default_language (все строки в коде и бд при разработке пишутся на этом языке)
// Если нет запрошенного языка --> показываем fallback_language
// Если нет fallback_language --> показываем вместо перевода not_translated_string

return [
    'default_language' => 'ru',
    'fallback_language' => 'en',
    'not_translated_string' => '[Not translated]',
    'i18n_cache_time' => 60, // сек (json для фронта)

    'deepl_api_key' => env('DEEPL_API_KEY', 'cd988b78-911d-cb35-d671-fbfdbabfd5c0:fx'),
    'deepl_api_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com/v2'),

    'poeditor_api_token' => env('POEDITOR_API_TOKEN', ''),
    'poeditor_project_id' => env('POEDITOR_PROJECT_ID', ''),

    'log_channel' => 'single',  // каналы в logging.php

    'deepl_in_background' => true,
    'deepl_queue' => 'default',
];
