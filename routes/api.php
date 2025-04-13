<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController; // Thêm import cho AdminController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::get('/fetch_users', [UserController::class, 'fetchUsers']);

// 1) Routes công khai cho người dùng thông thường (khách hàng)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 2) Route công khai: Lấy danh sách sản phẩm phổ biến, tìm kiếm, danh mục, và đánh giá
Route::get('/products/popular-public', [ProductController::class, 'getPopularProducts']);
Route::get('/search', [ProductController::class, 'search']);
Route::get('/categories', [DanhMucController::class, 'getCategories']);
Route::get('/products/{id_sanPham}/reviews', [ReviewController::class, 'index']); // Xem đánh giá của sản phẩm (công khai)

// 3) Routes yêu cầu xác thực (middleware 'auth:sanctum') cho người dùng thông thường
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/users', [AuthController::class, 'getUsers']); // Danh sách người dùng
    Route::get('/admins', [AuthController::class, 'getAdmins']); // Danh sách Admin
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user/update', [AuthController::class, 'updateUser']);
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
    Route::get('/favorites', [FavoriteController::class, 'index']); // Lấy danh sách yêu thích
    Route::post('/favorites/{productId}', [FavoriteController::class, 'add']); // Thêm sản phẩm yêu thích
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']); // Xóa sản phẩm yêu thích

    // Thông báo (cho người dùng)
    Route::get('/notifications', [NotificationController::class, 'index']); // Lấy danh sách thông báo
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']); // Đánh dấu thông báo là đã đọc
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy']); // Xóa thông báo

    // Đánh giá
    Route::post('/reviews', [ReviewController::class, 'store']); // Gửi đánh giá
    Route::get('/user/reviews', [ReviewController::class, 'userReviews']); // Xem đánh giá của người dùng hiện tại

    // Chat (gửi và nhận tin nhắn cho người dùng)
    Route::post('/messages', [MessageController::class, 'sendMessage']); // Gửi tin nhắn
    Route::get('/messages/{receiverId}', [MessageController::class, 'getMessages']); // Lấy danh sách tin nhắn
    Route::post('/messages/{messageId}/read', [MessageController::class, 'markAsRead']); // Đánh dấu tin nhắn là đã đọc

    // API cho admin lấy tin nhắn (dùng trong chat.index.blade.php)
    Route::get('/admin/messages/{userId}', [AdminController::class, 'getMessages']); // Lấy tin nhắn giữa admin và người dùng
});

// 4) Routes cho admin (qua API)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/notifications', [NotificationController::class, 'store']); // Admin tạo thông báo
    Route::delete('/admin/notifications/{notificationId}', [NotificationController::class, 'destroyAdmin']); // Admin xóa thông báo
});