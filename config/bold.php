<?php

return [
    'api_key' => env('BOLD_API_KEY'),
    'api_secret' => env('BOLD_API_SECRET'),
    'webhook_secret' => env('BOLD_WEBHOOK_SECRET'),
    'environment' => env('BOLD_ENVIRONMENT', 'sandbox'),
    'base_url' => env('BOLD_ENVIRONMENT') === 'production' 
        ? 'https://api.bold.co' 
        : 'https://api-sandbox.bold.co',
];
