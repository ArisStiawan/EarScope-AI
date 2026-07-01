<?php

use App\Http\Controllers\Api\EarscopeApiController;
use Illuminate\Support\Facades\Route;

/*
*/

// Menerima hasil diagnosis dari Flask (POST, tanpa CSRF)
Route::post('/earscope/diagnosis-result', [EarscopeApiController::class, 'receive'])
    ->name('api.earscope.receive');

// Polling hasil terbaru oleh halaman diagnosa dokter (GET)
Route::get('/earscope/latest-result', [EarscopeApiController::class, 'latest'])
    ->name('api.earscope.latest');

// Menerima foto capture dari Flask (POST)
Route::post('/earscope/upload-photo', [EarscopeApiController::class, 'uploadPhoto'])
    ->name('api.earscope.upload_photo');