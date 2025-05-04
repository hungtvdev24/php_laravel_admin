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
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\VNPayController;

Route::get('/fetch_users', [UserAuthController::class, 'fetchUsers']);

// Routes công khai
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products/popular-public', [ProductController::class, 'getPopularProducts']);
Route::get('/search', [ProductController::class, 'search']);
Route::get('/categories', [DanhMucController::class, 'getCategories']);
Route::get('/products/{id_sanPham}/reviews', [ReviewController::class, 'index']);

// Routes yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::get('/admins', [AuthController::class, 'getAdmins']);
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
    Route::get('/orders/{id}/reviews/{productId}/{variationId}', [ReviewController::class, 'checkReviewStatus']);

    // VNPay
    Route::post('/vnpay/create-payment', [VNPayController::class, 'createPayment']);
    Route::get('/vnpay/return', [VNPayController::class, 'returnPayment']);

    // Voucher
    Route::get('/vouchers', [VoucherController::class, 'index']);

    // Địa chỉ
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id_diaChi}', [AddressController::class, 'show']);
    Route::put('/addresses/{id_diaChi}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id_diaChi}', [AddressController::class, 'destroy']);

    // Sản phẩm yêu thích
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{productId}', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']);

    // Thông báo
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy']);

    // Đánh giá
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/user/reviews', [ReviewController::class, 'userReviews']);

    // Chat
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/messages/{receiverId}', [MessageController::class, 'getMessages']);
    Route::post('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);

    // Admin messages
    Route::get('/admin/messages/{userId}', [AdminController::class, 'getMessages']);
});

// Routes cho admin
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/notifications', [NotificationController::class, 'store']);
    Route::delete('/admin/notifications/{notificationId}', [NotificationController::class, 'destroyAdmin']);
    Route::post('/admin/vouchers', [VoucherController::class, 'store']);
    Route::delete('/admin/vouchers/{id}', [VoucherController::class, 'destroyAdmin']);
});