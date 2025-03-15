<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_donHang'); // Liên kết với đơn hàng
            $table->enum('trangThaiDonHang', ['cho_xac_nhan', 'dang_giao', 'da_giao']);
            $table->timestamp('ngay_cap_nhat')->useCurrent();
            $table->text('ghiChu')->nullable();
            $table->timestamps();

            // Thiết lập khóa ngoại với bảng đơn hàng
            $table->foreign('id_donHang')
                  ->references('id_donHang')
                  ->on('donHang')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_status_histories');
    }
};
