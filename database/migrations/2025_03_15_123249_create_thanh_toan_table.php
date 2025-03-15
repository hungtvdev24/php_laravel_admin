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
        Schema::create('thanhToan', function (Blueprint $table) {
            $table->id('id_thanhToan'); // Khóa chính của bảng thanh toán

            // Liên kết với đơn hàng (giả sử khóa chính của bảng donHang là id_donHang)
            $table->unsignedBigInteger('id_donHang');

            // Số tiền thanh toán
            $table->decimal('soTien', 10, 2);

            // Phương thức thanh toán: COD hoặc VN_PAY (thanh toán khi nhận hàng, thanh toán qua VN Pay)
            $table->enum('phuongThucThanhToan', ['COD', 'VN_PAY'])->default('COD');

            // Lưu thông tin QR code nếu sử dụng VN_PAY (có thể để NULL nếu không dùng)
            $table->string('qr_code')->nullable();

            // Trạng thái thanh toán: pending (chờ xử lý), success (thành công), failed (thất bại)
            $table->enum('trangThaiThanhToan', ['pending', 'success', 'failed'])->default('pending');

            $table->timestamps();

            // Thiết lập khóa ngoại liên kết với bảng đơn hàng
            $table->foreign('id_donHang')->references('id_donHang')->on('donHang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanhToan');
    }
};
