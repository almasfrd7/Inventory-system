<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Public: login only (5 attempts per minute per IP).
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Everything below needs a valid Sanctum token.
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Must be above apiResource, otherwise "stats" is treated as a product ID.
    Route::get('products/stats', [ProductController::class, 'stats']);
    Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);

    Route::apiResource('products', ProductController::class);
});
