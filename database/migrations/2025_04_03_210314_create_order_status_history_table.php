<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderStatusHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_donHang');
            $table->string('trangThaiDonHang');
            $table->timestamp('ngayThayDoi')->useCurrent();
            $table->text('ghiChu')->nullable();
            $table->foreign('id_donHang')->references('id_donHang')->on('donHang')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_status_history');
    }
}