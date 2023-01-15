<?php

return [
    'enabled' => env('SERVICE_ENABLED', false),
    'path' => env('SERVICE_PATH', 'services'),
    'token' => env('SERVICE_TOKEN'),
    'endpoints' => [
//        'https://example.com/endpoint' => [
//            \App\Models\User\User::class
//        ]
    ]
];