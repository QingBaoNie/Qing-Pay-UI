<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/EnvLoader.php';
EnvLoader::load();

return [
    'merchant_id' => EnvLoader::get('MERCHANT_ID'),
    'api_key' => EnvLoader::get('API_KEY'),
    'notify_url' => EnvLoader::get('NOTIFY_URL'),
    'return_url' => EnvLoader::get('RETURN_URL'),
    'payment_url' => EnvLoader::get('PAYMENT_URL')
];
