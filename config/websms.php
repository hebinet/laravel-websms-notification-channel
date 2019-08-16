<?php

return [
    'gateway' => env('WEBSMS_GATEWAY', 'https://api.websms.com'),

    // Tokenauth
    'token' => env('WEBSMS_TOKEN', null),
    // or Username/Password auth
    'username' => env('WEBSMS_USERNAME', null),
    'password' => env('WEBSMS_PASSWORD', null),

    'test' => env('WEBSMS_TEST', true)
];