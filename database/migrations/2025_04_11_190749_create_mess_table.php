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
        Schema::create('mess', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id'); // Người gửi
            $table->unsignedBigInteger('receiver_id'); // Người nhận
            $table->text('content'); // Nội dung tin nhắn
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc
            $table->timestamps(); // created_at và updated_at

            // Ràng buộc khóa ngoại
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mess');
    }
};