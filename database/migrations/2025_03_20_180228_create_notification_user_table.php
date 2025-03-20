<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng notifications
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng users
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc hay chưa
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_user');
    }
};