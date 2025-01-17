<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/test', [AuthController::class, 'test'])->middleware(['auth:api', 'role:user,admin']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'duitku'
], function () {
    Route::post('/payment-methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/payment/create', [PaymentController::class, 'create']);
    Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/payment/redirect', [PaymentController::class, 'redirect'])->name('payment.redirect');
});
Route::middleware('auth:api')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store'])->middleware('role:admin');
    Route::get('/products/{product_id}', [ProductController::class, 'show']);
    Route::post('/products/{product_id}', [ProductController::class, 'update'])->middleware('role:admin');
    Route::delete('/products/{product_id}', [ProductController::class, 'destroy'])->middleware('role:admin');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart', [CartController::class, 'addItem']);
    Route::delete('/cart/item/{product_id}', [CartController::class, 'removeItem']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order_id}', [OrderController::class, 'show']);
    Route::put('/orders/{order_id}', [OrderController::class, 'update'])->middleware('role:admin');
});
