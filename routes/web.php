<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Chuyển hướng trang chủ đến trang đăng nhập
Route::get('/', function () {
    return redirect()->route('login');
});

// Đăng nhập và đăng xuất Admin
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logoutAdmin'])->name('admin.logout');

// Middleware bảo vệ Admin
Route::middleware(['admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Quản lý người dùng
    Route::get('/admin/users', [AdminController::class, 'manageUsers'])->name('admin.users.index');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Quản lý sản phẩm
    Route::prefix('/admin/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    });

    // Quản lý danh mục
    Route::prefix('/admin/danhmucs')->group(function () {
        Route::get('/', [DanhMucController::class, 'index'])->name('admin.danhmucs.index');
        Route::get('/create', [DanhMucController::class, 'create'])->name('admin.danhmucs.create');
        Route::post('/', [DanhMucController::class, 'store'])->name('admin.danhmucs.store');
        Route::get('/{id_danhMuc}/edit', [DanhMucController::class, 'edit'])->name('admin.danhmucs.edit');
        Route::put('/{id_danhMuc}', [DanhMucController::class, 'update'])->name('admin.danhmucs.update');
        Route::delete('/{id_danhMuc}', [DanhMucController::class, 'destroy'])->name('admin.danhmucs.destroy');
    });

    // Quản lý tiếp thị liên kết (Affiliate)
    Route::prefix('/admin/affiliate')->group(function () {
        // Route cũ: Hiển thị danh sách affiliate
        Route::get('/', [AdminController::class, 'manageAffiliate'])->name('admin.affiliate.index');

        // Route mới: Quản lý thông báo (notifications)
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'indexAdmin'])->name('admin.affiliate.notifications.index'); // Danh sách thông báo
            Route::get('/create', [NotificationController::class, 'createAdmin'])->name('admin.affiliate.notifications.create'); // Tạo thông báo mới
            Route::post('/store', [NotificationController::class, 'storeAdmin'])->name('admin.affiliate.notifications.store'); // Lưu thông báo mới
            Route::get('/{id}', [NotificationController::class, 'showAdmin'])->name('admin.affiliate.notifications.detail'); // Xem chi tiết thông báo
        });
    });

    // Quản lý chiến dịch
    Route::get('/admin/campaigns', [AdminController::class, 'manageCampaigns'])->name('admin.campaigns.index');

    // Quản lý dịch vụ
    Route::get('/admin/services', [AdminController::class, 'manageServices'])->name('admin.services.index');

    // Quản lý giao dịch
    Route::get('/admin/transactions', [AdminController::class, 'manageTransactions'])->name('admin.transactions.index');

    // Quản lý khách hàng
    Route::get('/admin/customers', [AdminController::class, 'manageCustomers'])->name('admin.customers.index');

    // Quản lý nhân viên
    Route::get('/admin/employees', [EmployeeController::class, 'index'])->name('admin.employees.index');

    // Quản lý đơn hàng
    Route::prefix('/admin/orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index'); // Danh sách đơn hàng
        Route::get('/{id}/view', [OrderController::class, 'viewOrder'])->name('admin.orders.view'); // Xem chi tiết đơn hàng
        Route::put('/{id}', [OrderController::class, 'update'])->name('admin.orders.update'); // Cập nhật trạng thái đơn hàng
        Route::post('/{id}/cancel', [OrderController::class, 'adminCancelOrder'])->name('admin.orders.cancel'); // Hủy đơn hàng
    });

    // Quản lý khuyến mãi
    Route::get('/admin/promotions', [AdminController::class, 'managePromotions'])->name('admin.promotions.index');

    // Quản lý thống kê
    Route::get('/admin/statistics', [AdminController::class, 'statisticsOrders'])->name('admin.statistics.index');
});