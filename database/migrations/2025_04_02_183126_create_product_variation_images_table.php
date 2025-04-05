<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('product_variation_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variation_id');
            $table->string('image_url');
            $table->timestamps();

            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variation_images');
    }
};