<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('donHang', function (Blueprint $table) {
            $table->id('id_donHang');
            $table->unsignedBigInteger('id_nguoiDung'); // Liên kết với bảng users

            // Snapshot thông tin địa chỉ giao hàng
            $table->string('ten_nguoiNhan');
            $table->string('sdt_nhanHang');
            $table->string('ten_nha');
            $table->string('tinh');
            $table->string('huyen');
            $table->string('xa');

            $table->decimal('tongTien', 10, 2)->default(0);
            $table->string('phuongThucThanhToan')->nullable();

            // Trạng thái đơn hàng: chờ xác nhận, đang giao, đã giao
            $table->enum('trangThaiDonHang', ['cho_xac_nhan', 'dang_giao', 'da_giao'])
                  ->default('cho_xac_nhan');

            $table->timestamps();

            $table->foreign('id_nguoiDung')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('donHang');
    }
};
