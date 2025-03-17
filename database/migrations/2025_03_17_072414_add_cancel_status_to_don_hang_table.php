<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Cập nhật cột trangThaiDonHang để thêm giá trị 'huy'
        DB::statement("ALTER TABLE donHang MODIFY trangThaiDonHang ENUM('cho_xac_nhan', 'dang_giao', 'da_giao', 'huy') NOT NULL DEFAULT 'cho_xac_nhan'");
    }

    public function down()
    {
        // Trở lại enum ban đầu nếu cần rollback
        DB::statement("ALTER TABLE donHang MODIFY trangThaiDonHang ENUM('cho_xac_nhan', 'dang_giao', 'da_giao') NOT NULL DEFAULT 'cho_xac_nhan'");
    }
};
