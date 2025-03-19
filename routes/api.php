<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Dưới đây là các route cho API của bạn. Các route công khai (không cần xác thực)
| được định nghĩa ở mục 1 và 2. Các route yêu cầu xác thực (middleware 'auth:sanctum')
| được đặt trong nhóm middleware.
|
*/

// 1) Routes công khai
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2) Route công khai ví dụ: Lấy danh sách sản phẩm phổ biến
Route::get('/products/popular-public', [ProductController::class, 'getPopularProducts']);

// 3) Routes yêu cầu xác thực (middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user/update', [AuthController::class, 'updateUser']); // New route for updating user info
    Route::post('/logout', [AuthController::class, 'logout']);

    // Sản phẩm
    Route::get('/products/popular', [ProductController::class, 'getPopularProducts']);

    // Giỏ hàng
    Route::post('/cart/add/{idSanPham}', [ProductController::class, 'addToCart']);
    Route::get('/cart', [ProductController::class, 'getCart']);
    Route::post('/cart/update/{idMucGioHang}', [ProductController::class, 'updateCartItem']);
    Route::post('/cart/remove/{idMucGioHang}', [ProductController::class, 'removeCartItem']);

    // Đơn hàng
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'showOrder']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Địa chỉ
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id_diaChi}', [AddressController::class, 'show']);
    Route::put('/addresses/{id_diaChi}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id_diaChi}', [AddressController::class, 'destroy']);

    // Sản phẩm yêu thích
    Route::get('/favorite', [FavoriteController::class, 'index']);
    Route::post('/favorite/add/{productId}', [FavoriteController::class, 'add']);
    Route::post('/favorite/remove/{productId}', [FavoriteController::class, 'remove']);
});