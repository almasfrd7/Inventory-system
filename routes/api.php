<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Dashboard statistics (total products, total stock, low stock).
// This MUST be above apiResource, otherwise "stats" would be
// treated as a product ID by GET /api/products/{product}.
Route::get(
    'products/stats',
    [ProductController::class, 'stats']
);

// Adjust stock without changing the other product details.
Route::post(
    'products/{product}/adjust-stock',
    [ProductController::class, 'adjustStock']
);

// This single line creates the REST endpoints
Route::apiResource('products', ProductController::class);
// GET     /api/products   (search, filter, sort, pagination)
// POST    /api/products
// GET     /api/products/{product}
// PUT     /api/products/{product}
// PATCH   /api/products/{product}
// DELETE  /api/products/{product}