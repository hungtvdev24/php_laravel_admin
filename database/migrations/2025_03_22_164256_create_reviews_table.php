<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('id_danhGia');
            $table->unsignedBigInteger('id_nguoiDung');
            $table->unsignedBigInteger('id_sanPham');
            $table->unsignedBigInteger('id_donHang');
            $table->integer('soSao')->unsigned(); // Số sao từ 1-5
            $table->text('binhLuan')->nullable(); // Bình luận (không bắt buộc)
            $table->string('urlHinhAnh')->nullable(); // URL hình ảnh (không bắt buộc)
            $table->timestamp('ngayDanhGia')->useCurrent();
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('id_nguoiDung')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_sanPham')->references('id_sanPham')->on('products')->onDelete('cascade');
            $table->foreign('id_donHang')->references('id_donHang')->on('donHang')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}