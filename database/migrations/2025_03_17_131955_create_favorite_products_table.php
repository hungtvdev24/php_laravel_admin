<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('favorite_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            // Thiết lập khóa ngoại:
            // user_id liên kết với cột id của bảng users
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // product_id liên kết với cột id_sanPham của bảng products
            $table->foreign('product_id')
                  ->references('id_sanPham')
                  ->on('products')
                  ->onDelete('cascade');

            // Đảm bảo mỗi người dùng chỉ yêu thích 1 sản phẩm một lần
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('favorite_products');
    }
};
