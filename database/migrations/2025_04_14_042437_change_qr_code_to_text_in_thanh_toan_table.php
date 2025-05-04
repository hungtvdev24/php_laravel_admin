<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thanhToan', function (Blueprint $table) {
            // Thay đổi cột qr_code từ VARCHAR(255) sang TEXT, vẫn giữ nullable
            $table->text('qr_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thanhToan', function (Blueprint $table) {
            // Hoàn tác về VARCHAR(255) nếu cần
            $table->string('qr_code', 255)->nullable()->change();
        });
    }
};