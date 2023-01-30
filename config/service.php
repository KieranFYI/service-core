<?php

return [
    'enabled' => env('SERVICE_ENABLED', false),
    'encrypt' => env('SERVICE_ENCRYPT', true),
    'path' => env('SERVICE_PATH', 'services'),
    'endpoints' => [
//        'https://example.com/endpoint' => [
//            \App\Models\User\User::class
//        ]
    ]
];