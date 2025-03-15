<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController; // Import OrderController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Routes không yêu cầu xác thực
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2. Route công khai để lấy danh sách sản phẩm phổ biến (không yêu cầu xác thực)
Route::get('/products/popular-public', [ProductController::class, 'getPopularProducts']);

// 3. Routes yêu cầu xác thực (middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {

    // Lấy danh sách người dùng, thông tin người dùng, đăng xuất
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Lấy danh sách sản phẩm phổ biến (có thể yêu cầu xác thực)
    Route::get('/products/popular', [ProductController::class, 'getPopularProducts']);

    // Routes liên quan đến giỏ hàng
    Route::post('/cart/add/{idSanPham}', [ProductController::class, 'addToCart']);
    Route::get('/cart', [ProductController::class, 'getCart']);

    // ----------------- ROUTES LIÊN QUAN ĐẾN ĐƠN HÀNG -----------------
    // Endpoint gửi đơn hàng (checkout)
    Route::post('/checkout', [OrderController::class, 'checkout']);

    // ----------------- ROUTES LIÊN QUAN ĐẾN ĐỊA CHỈ -----------------
    Route::get('/addresses', [AddressController::class, 'index']);        // Lấy danh sách địa chỉ
    Route::post('/addresses', [AddressController::class, 'store']);       // Tạo địa chỉ mới
    Route::get('/addresses/{id_diaChi}', [AddressController::class, 'show']);    // Xem 1 địa chỉ
    Route::put('/addresses/{id_diaChi}', [AddressController::class, 'update']);  // Cập nhật địa chỉ
    Route::delete('/addresses/{id_diaChi}', [AddressController::class, 'destroy']); // Xóa địa chỉ
});
