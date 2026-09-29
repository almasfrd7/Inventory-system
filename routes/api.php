<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Adjust stock without changing the other product details.
Route::post(
    'products/{product}/adjust-stock',
    [ProductController::class, 'adjustStock']
);

// This single line creates the REST endpoints
Route::apiResource('products', ProductController::class);
// GET     /api/products
// POST    /api/products
// GET     /api/products/{product}
// PUT     /api/products/{product}
// PATCH   /api/products/{product}
// DELETE  /api/products/{product}

