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
        Schema::create('san_phams', function (Blueprint $table) {
            $table->id(); // id sản phẩm (id_sanPham)
            $table->string('ten_san_pham'); // Tên sản phẩm
            $table->text('mo_ta')->nullable(); // Mô tả sản phẩm
            $table->decimal('gia', 15, 2); // Giá sản phẩm
            $table->integer('so_luong')->default(0); // Số lượng tồn kho
            $table->string('hinh_anh')->nullable(); // Hình ảnh sản phẩm
            $table->unsignedBigInteger('danh_muc_id'); // Liên kết danh mục

            // Khóa ngoại liên kết với bảng danh mục
            $table->foreign('danh_muc_id')->references('id')->on('danh_mucs')->onDelete('cascade');

            $table->timestamps(); // Ngày tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('san_phams');
    }
};
