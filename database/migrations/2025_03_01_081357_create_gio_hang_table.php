<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('gioHang')) {
            Schema::create('gioHang', function (Blueprint $table) {
                $table->id('id_gioHang'); // Khóa chính (id giỏ hàng)
                $table->unsignedBigInteger('id_nguoiDung'); // Khóa ngoại đến bảng users
                $table->timestamps(); // Thời gian tạo & cập nhật

                // Thiết lập khóa ngoại
                $table->foreign('id_nguoiDung')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('gioHang');
    }
};