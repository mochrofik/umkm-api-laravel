<?php

use Illuminate\Support\Facades\Route;


Route::get('/debug-php', function() {
    return [
        'php_version' => phpversion(),
        'bcrypt_supported' => defined('PASSWORD_BCRYPT'),
        'openssl_enabled' => extension_loaded('openssl'),
    ];
});