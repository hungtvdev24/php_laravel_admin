<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('userNameAD')->unique();
            $table->string('passwordAD');
            $table->integer('roleID'); // 1: Chủ cửa hàng, 2: Nhân viên
            $table->timestamps();
        });

        // Seed an initial admin user
        \DB::table('admins')->insert([
            'userNameAD' => 'admin',
            'passwordAD' => Hash::make('123456'),
            'roleID' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
