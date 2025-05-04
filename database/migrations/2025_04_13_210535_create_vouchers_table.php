<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id(); // ID voucher
            $table->string('code')->unique(); // Mã voucher
            $table->decimal('discount_value', 10, 2); // Giá trị giảm giá
            $table->enum('discount_type', ['fixed', 'percentage']); // Loại giảm giá
            $table->decimal('min_order_value', 10, 2)->nullable(); // Giá trị đơn hàng tối thiểu
            $table->decimal('max_discount', 10, 2)->nullable(); // Giá trị giảm tối đa
            $table->timestamp('start_date')->nullable(); // Thời gian bắt đầu
            $table->timestamp('end_date')->nullable(); // Thời gian kết thúc
            $table->integer('usage_limit')->nullable(); // Số lần sử dụng tối đa
            $table->integer('used_count')->default(0); // Số lần đã sử dụng
            $table->enum('status', ['active', 'inactive', 'expired']); // Trạng thái
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};