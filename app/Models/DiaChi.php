<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'diaChi';          // Tên bảng
    protected $primaryKey = 'id_diaChi';  // Khóa chính
    public $timestamps = true;            // Sử dụng timestamps

    protected $fillable = [
        'id_nguoiDung',
        'sdt_nhanHang',
        'ten_nguoiNhan',
        'ten_nha',
        'tinh',
        'huyen',
        'xa',
    ];

    // Nếu bạn cần định nghĩa quan hệ với bảng users:
    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoiDung', 'id');
    }
}
