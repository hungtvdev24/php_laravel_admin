<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root URL to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Login route
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Admin authentication routes
Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logoutAdmin'])->name('admin.logout');

// Admin routes (protected by 'admin' middleware)
Route::middleware(['admin'])->prefix('/admin')->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard/filter', [AdminController::class, 'filterDashboard'])->name('admin.dashboard.filter');

    // User management routes
    Route::get('/users', [AdminController::class, 'manageUsers'])->name('admin.users.index');
    Route::middleware('role:admin')->delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Product management routes
    Route::prefix('/products')->group(function () {
        Route::get('/', [AdminController::class, 'manageProducts'])->name('admin.products.index');
        Route::middleware('role:admin')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('admin.products.create');
            Route::post('/', [ProductController::class, 'store'])->name('admin.products.store');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
            Route::put('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        });
    });

    // Category (DanhMuc) management routes
    Route::prefix('/danhmucs')->group(function () {
        Route::get('/', [DanhMucController::class, 'index'])->name('admin.danhmucs.index');
        Route::middleware('role:admin')->group(function () {
            Route::get('/create', [DanhMucController::class, 'create'])->name('admin.danhmucs.create');
            Route::post('/', [DanhMucController::class, 'store'])->name('admin.danhmucs.store');
            Route::get('/{id_danhMuc}/edit', [DanhMucController::class, 'edit'])->name('admin.danhmucs.edit');
            Route::put('/{id_danhMuc}', [DanhMucController::class, 'update'])->name('admin.danhmucs.update');
            Route::delete('/{id_danhMuc}', [DanhMucController::class, 'destroy'])->name('admin.danhmucs.destroy');
        });
    });

    // Affiliate management routes
    Route::prefix('/affiliate')->group(function () {
        Route::get('/', [AdminController::class, 'manageAffiliate'])->name('admin.affiliate.index');

        // Notification management routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'indexAdmin'])->name('admin.affiliate.notifications.index');
            Route::get('/create', [NotificationController::class, 'createAdmin'])->name('admin.affiliate.notifications.create'); // Bỏ middleware role:admin
            Route::post('/', [NotificationController::class, 'storeAdmin'])->name('admin.affiliate.notifications.store');
            Route::get('/{id}', [NotificationController::class, 'showAdmin'])->name('admin.affiliate.notifications.detail');
            Route::middleware('role:admin')->group(function () {
                Route::delete('/{id}', [NotificationController::class, 'destroyAdmin'])->name('admin.affiliate.notifications.destroy');
            });
        });

        // Voucher management routes
        Route::prefix('vouchers')->group(function () {
            Route::get('/', [VoucherController::class, 'indexAdmin'])->name('admin.affiliate.vouchers.index');
            Route::middleware('role:admin')->group(function () {
                Route::get('/create', [VoucherController::class, 'createAdmin'])->name('admin.affiliate.vouchers.create');
                Route::post('/', [VoucherController::class, 'storeAdmin'])->name('admin.affiliate.vouchers.store');
                Route::patch('/{id}/status', [VoucherController::class, 'updateStatus'])->name('admin.affiliate.vouchers.updateStatus');
                Route::delete('/{id}', [VoucherController::class, 'destroyAdmin'])->name('admin.affiliate.vouchers.destroy');
            });
            Route::get('/{id}', [VoucherController::class, 'showAdmin'])->name('admin.affiliate.vouchers.show');
        });
    });

    // Review management routes
    Route::prefix('/reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'indexAdmin'])->name('admin.reviews.index');
        Route::get('/{id}', [ReviewController::class, 'showAdmin'])->name('admin.reviews.show');
        Route::middleware('role:admin')->group(function () {
            Route::patch('/{id}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
            Route::patch('/{id}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
            Route::delete('/{id}', [ReviewController::class, 'destroyAdmin'])->name('admin.reviews.destroy');
        });
    });

    // Campaign management routes
    Route::get('/campaigns', [AdminController::class, 'manageCampaigns'])->name('admin.campaigns.index');

    // Service management routes
    Route::get('/services', [AdminController::class, 'manageServices'])->name('admin.services.index');

    // Transaction management routes
    Route::get('/transactions', [AdminController::class, 'manageTransactions'])->name('admin.transactions.index');

    // Customer management routes
    Route::get('/customers', [AdminController::class, 'manageCustomers'])->name('admin.customers.index');

    // Employee management routes
    Route::prefix('/employees')->group(function () {
        Route::get('/', [AdminController::class, 'manageEmployees'])->name('admin.employees.index');
        Route::get('/create', [AdminController::class, 'createEmployee'])->name('admin.employees.create');
        Route::post('/', [AdminController::class, 'storeEmployee'])->name('admin.employees.store');
        Route::get('/{id}/edit', [AdminController::class, 'editEmployee'])->name('admin.employees.edit');
        Route::put('/{id}', [AdminController::class, 'updateEmployee'])->name('admin.employees.update');
        Route::delete('/{id}', [AdminController::class, 'destroyEmployee'])->name('admin.employees.destroy');
    });

    // Order management routes
    Route::prefix('/orders')->group(function () {
        Route::get('/', [AdminController::class, 'manageOrders'])->name('admin.orders.index');
        Route::get('/{id}/view', [AdminController::class, 'orderDetails'])->name('admin.orders.view');
        Route::put('/{id}', [AdminController::class, 'updateOrder'])->name('admin.orders.update');
        Route::get('/{id}/export-invoice', [OrderController::class, 'exportInvoice'])->name('admin.orders.exportInvoice');
    });

    // Promotion management routes
    Route::get('/promotions', [AdminController::class, 'managePromotions'])->name('admin.promotions.index');

    // Statistics routes
    Route::get('/statistics', [AdminController::class, 'statisticsOrders'])->name('admin.statistics.index');

    // Admin Notification management routes (renamed to avoid conflict)
    Route::prefix('/admin-notifications')->group(function () {
        Route::get('/', [AdminController::class, 'manageNotifications'])->name('admin.notifications.index');
        Route::post('/send', [AdminController::class, 'sendNotification'])->name('admin.notifications.send');
    });

    // Chat management routes
    Route::prefix('/chat')->group(function () {
        Route::get('/', [AdminController::class, 'chatIndex'])->name('admin.chat.index');
        Route::get('/{userId}', [AdminController::class, 'chatWithUser'])->name('admin.chat.show');
        Route::post('/send/{userId}', [AdminController::class, 'sendMessage'])->name('admin.chat.send');
    });
});