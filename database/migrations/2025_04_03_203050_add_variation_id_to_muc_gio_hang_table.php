<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariationIdToMucGioHangTable extends Migration
{
    public function up()
    {
        Schema::table('mucGioHang', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->after('id_sanPham');
            $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('mucGioHang', function (Blueprint $table) {
            $table->dropForeign(['variation_id']);
            $table->dropColumn('variation_id');
        });
    }
}