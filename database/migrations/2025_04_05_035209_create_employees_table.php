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
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id('id_nhanVien'); // Khóa chính
                $table->string('tenNhanVien')->comment('Tên nhân viên'); // Tên nhân viên
                $table->integer('tuoi')->comment('Tuổi'); // Tuổi
                $table->string('diaChi')->comment('Địa chỉ'); // Địa chỉ
                $table->string('tenTaiKhoan')->unique()->comment('Tên tài khoản'); // Tên tài khoản duy nhất
                $table->string('matKhau')->comment('Mật khẩu'); // Mật khẩu
                $table->enum('trangThai', ['active', 'inactive'])->default('active')->comment('Trạng thái'); // Trạng thái nhân viên
                $table->foreignId('id_admin')->constrained('admins')->onDelete('cascade')->comment('Admin tạo tài khoản'); // Khóa ngoại đến bảng admins
                $table->timestamps(); // Thời gian tạo và cập nhật
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};