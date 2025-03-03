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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // id người dùng (id_nguoiDung)
            $table->string('name'); // Họ và tên đầy đủ
            $table->string('email')->unique(); // Email
            $table->string('phone')->nullable(); // Số điện thoại
            $table->string('password'); // Mật khẩu
            $table->integer('tuoi')->nullable(); // Tuổi
            $table->timestamps(); // Thời gian tạo & cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
