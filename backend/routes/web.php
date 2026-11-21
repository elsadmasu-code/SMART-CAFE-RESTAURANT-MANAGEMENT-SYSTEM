<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'system' => 'Smart Cafe & Restaurant Management System (SCRMS)',
        'enterprise' => 'Fkr Cafe',
        'location' => 'Kombolcha, Amhara, Ethiopia',
        'status' => 'Operational',
        'version' => '1.0',
        'api_documentation' => '/api/v1/menu',
    ]);
});
