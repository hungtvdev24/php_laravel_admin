<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donHang', function (Blueprint $table) {
            // Thêm cột ngày giao hàng dự kiến và ngày giao hàng thực tế
            $table->timestamp('ngay_du_kien_giao')->nullable()->after('trangThaiDonHang');
            $table->timestamp('ngay_giao_thuc_te')->nullable()->after('ngay_du_kien_giao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donHang', function (Blueprint $table) {
            // Khi rollback, xóa các cột đã thêm
            $table->dropColumn(['ngay_du_kien_giao', 'ngay_giao_thuc_te']);
        });
    }
};
