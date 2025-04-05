<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariationIdToChiTietDonHangTable extends Migration
{
    public function up()
    {
        Schema::table('chiTietDonHang', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->after('id_sanPham');
            $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('chiTietDonHang', function (Blueprint $table) {
            $table->dropForeign(['variation_id']);
            $table->dropColumn('variation_id');
        });
    }
}