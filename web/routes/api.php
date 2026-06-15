<?php

use App\Http\Controllers\Api\EarscopeApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Earscope Integration
|--------------------------------------------------------------------------
| Endpoint ini dipanggil oleh Flask app (earscope-model) setelah
| selesai merekam video dan menjalankan deteksi YOLO.
| Tidak memerlukan autentikasi Laravel (dijaga via APP_KEY di Flask).
*/

// Menerima hasil diagnosis dari Flask (POST, tanpa CSRF)
Route::post('/earscope/diagnosis-result', [EarscopeApiController::class, 'receive'])
    ->name('api.earscope.receive');

// Polling hasil terbaru oleh halaman diagnosa dokter (GET)
Route::get('/earscope/latest-result', [EarscopeApiController::class, 'latest'])
    ->name('api.earscope.latest');