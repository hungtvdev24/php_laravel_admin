<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;

    protected $table = 'thanhToan';      // Tên bảng trong CSDL
    protected $primaryKey = 'id_thanhToan'; // Khóa chính

    protected $fillable = [
        'id_donHang',
        'soTien',
        'phuongThucThanhToan',
        'qr_code',
        'trangThaiThanhToan'
    ];
}
