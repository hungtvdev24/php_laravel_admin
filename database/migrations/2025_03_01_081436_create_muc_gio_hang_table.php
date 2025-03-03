<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mucGioHang')) {
            Schema::create('mucGioHang', function (Blueprint $table) {
                $table->id('id_mucGioHang'); // Khóa chính (id mục giỏ hàng)
                $table->unsignedBigInteger('id_gioHang'); // Khóa ngoại đến bảng gioHang
                $table->unsignedBigInteger('id_sanPham'); // Khóa ngoại đến bảng products
                $table->integer('soLuong')->default(1); // Số lượng sản phẩm
                $table->decimal('gia', 10, 2)->default(0); // Giá tại thời điểm thêm vào giỏ
                $table->timestamps(); // Thời gian tạo & cập nhật

                // Thiết lập khóa ngoại
                $table->foreign('id_gioHang')->references('id_gioHang')->on('gioHang')->onDelete('cascade');
                $table->foreign('id_sanPham')->references('id_sanPham')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mucGioHang');
    }
};