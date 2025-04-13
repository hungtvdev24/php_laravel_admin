<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessTable extends Migration
{
    public function up()
    {
        Schema::create('mess', function (Blueprint $table) {
            $table->id();
            $table->morphs('sender'); // Tạo sender_id và sender_type
            $table->morphs('receiver'); // Tạo receiver_id và receiver_type
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mess');
    }
}