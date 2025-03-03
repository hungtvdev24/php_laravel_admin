<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes không yêu cầu xác thực
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route công khai để lấy danh sách sản phẩm phổ biến (không yêu cầu xác thực)
Route::get('/products/popular-public', [ProductController::class, 'getPopularProducts']);

// Routes yêu cầu xác thực (middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Route lấy danh sách sản phẩm phổ biến (yêu cầu xác thực, tùy chọn)
    Route::get('/products/popular', [ProductController::class, 'getPopularProducts']);

    // Routes liên quan đến giỏ hàng
    Route::post('/cart/add/{idSanPham}', [ProductController::class, 'addToCart']);
    Route::get('/cart', [ProductController::class, 'getCart']);
});