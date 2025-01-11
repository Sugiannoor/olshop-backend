<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/test', [AuthController::class, 'test'])->middleware(['auth:api', 'role:user,admin']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

// Protected routes for authenticated users
Route::middleware('auth:api')->group(function () {
    // Product Routes
    Route::get('/products', [ProductController::class, 'index']); // List all products
    Route::post('/products', [ProductController::class, 'store'])->middleware('role:admin'); // Create a new product
    Route::get('/products/{product_id}', [ProductController::class, 'show']); // Get details of a product
    Route::put('/products/{product_id}', [ProductController::class, 'update'])->middleware('role:admin'); // Update a product
    Route::delete('/products/{product_id}', [ProductController::class, 'destroy'])->middleware('role:admin'); // Delete a product

    // Cart Routes
    Route::get('/cart', [CartController::class, 'getCart']); // Get cart with items
    Route::post('/cart', [CartController::class, 'addItem']); // Add item to cart
    Route::delete('/cart/item/{item_id}', [CartController::class, 'removeItem']); // Remove item from cart
    Route::post('/cart/checkout', [CartController::class, 'checkout']); // Checkout cart

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index']); // List user's orders
    Route::post('/orders', [OrderController::class, 'store']); // Create a new order
    Route::get('/orders/{order_id}', [OrderController::class, 'show']); // View a single order
    Route::put('/orders/{order_id}', [OrderController::class, 'update']); // Update order status
});
