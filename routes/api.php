<?php

use App\Http\Controllers\Api\V1\BarangApiController;
use App\Http\Middleware\RateLimitMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware([RateLimitMiddleware::class])->group(function () {
    Route::get('/barang', [BarangApiController::class, 'index']);
    Route::get('/barang/{id}', [BarangApiController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
