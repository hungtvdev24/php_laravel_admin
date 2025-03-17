<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('donHang', function (Blueprint $table) {
            // Cập nhật cột enum trangThaiDonHang để bao gồm thêm trạng thái "huy"
            $table->enum('trangThaiDonHang', ['cho_xac_nhan', 'dang_giao', 'da_giao', 'huy'])
                  ->default('cho_xac_nhan')
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('donHang', function (Blueprint $table) {
            // Khôi phục lại enum ban đầu (không có trạng thái "huy")
            $table->enum('trangThaiDonHang', ['cho_xac_nhan', 'dang_giao', 'da_giao'])
                  ->default('cho_xac_nhan')
                  ->change();
        });
    }
};
