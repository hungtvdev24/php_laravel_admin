<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id('id_sanPham');
                $table->unsignedBigInteger('id_danhMuc'); // Khóa ngoại đến bảng danhMuc
                $table->string('tenSanPham');
                $table->string('thuongHieu');
                $table->decimal('gia', 10, 2)->default(0);
                $table->string('urlHinhAnh')->nullable(); // Cho phép NULL nếu không có ảnh
                $table->text('moTa')->nullable();
                $table->enum('trangThai', ['active', 'inactive'])->default('active'); // Trạng thái sản phẩm
                $table->timestamps();

                // Thiết lập khóa ngoại
                $table->foreign('id_danhMuc')->references('id_danhMuc')->on('danhMuc')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
