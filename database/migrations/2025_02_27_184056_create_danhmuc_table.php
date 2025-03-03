<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('danhMuc')) {
            Schema::create('danhMuc', function (Blueprint $table) {
                $table->id('id_danhMuc'); // Khóa chính
                $table->string('tenDanhMuc')->unique(); // Tên danh mục, không trùng lặp
                $table->text('moTa')->nullable(); // Mô tả danh mục, có thể để trống
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('danhMuc');
    }
};
