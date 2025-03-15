<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('diaChi')) {
            Schema::create('diaChi', function (Blueprint $table) {
                $table->id('id_diaChi'); // Khóa chính của bảng địa chỉ
                $table->unsignedBigInteger('id_nguoiDung'); // Khóa ngoại liên kết với bảng users

                $table->string('sdt_nhanHang');    // Số điện thoại nhận hàng
                $table->string('ten_nguoiNhan');   // Tên người nhận
                $table->string('ten_nha');         // Tên nhà (hoặc số nhà, tên đường nếu cần)
                $table->string('tinh');            // Tỉnh
                $table->string('huyen');           // Huyện
                $table->string('xa');              // Xã

                $table->timestamps(); // Thời gian tạo & cập nhật

                // Thiết lập khóa ngoại liên kết với bảng users
                $table->foreign('id_nguoiDung')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('diaChi');
    }
};
