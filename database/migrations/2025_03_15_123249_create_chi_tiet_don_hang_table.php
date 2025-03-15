<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chiTietDonHang', function (Blueprint $table) {
            $table->id('id_chiTietDonHang');
            $table->unsignedBigInteger('id_donHang');  // Liên kết với bảng đơn hàng
            $table->unsignedBigInteger('id_sanPham');    // Liên kết với bảng sản phẩm

            $table->integer('soLuong')->default(1);
            $table->decimal('gia', 10, 2)->default(0);    // Giá tại thời điểm đặt hàng

            $table->timestamps();

            $table->foreign('id_donHang')
                  ->references('id_donHang')
                  ->on('donHang')
                  ->onDelete('cascade');

            $table->foreign('id_sanPham')
                  ->references('id_sanPham')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chiTietDonHang');
    }
};

