<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng users
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng vouchers
            $table->timestamp('used_at')->useCurrent(); // Thời gian sử dụng
            $table->foreignId('order_id')->constrained('donHang', 'id_donHang')->onDelete('cascade'); // Khóa ngoại đến bảng donHang
            $table->unique(['user_id', 'voucher_id']); // Đảm bảo mỗi người dùng chỉ dùng voucher một lần
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_voucher_usage');
    }
};  