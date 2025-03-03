<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Kiểm tra xem admin đã tồn tại chưa, nếu chưa thì tạo mới
        if (!Admin::where('userNameAD', 'admin')->exists()) {
            Admin::create([
                'userNameAD' => 'admin',
                'passwordAD' => Hash::make('123456'),
                'roleID' => 1
            ]);
        }
    }
}
