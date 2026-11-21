<?php

return [
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ],

    'telebirr' => [
        'merchant_id' => env('TELEBIRR_MERCHANT_ID', ''),
        'app_key' => env('TELEBIRR_APP_KEY', ''),
        'public_key' => env('TELEBIRR_PUBLIC_KEY', ''),
    ],

    'cbe_birr' => [
        'merchant_code' => env('CBE_BIRR_MERCHANT_CODE', ''),
    ],

    'chapa' => [
        'secret_key' => env('CHAPA_SECRET_KEY', ''),
        'base_url' => 'https://api.chapa.co/v1',
    ],
];
