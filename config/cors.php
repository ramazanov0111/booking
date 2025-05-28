<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Замените на ваш домен в продакшене
    'allowed_headers' => ['*'],
    'allowed_credentials' => true
];
