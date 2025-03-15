<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $table = 'donHang';
    protected $primaryKey = 'id_donHang';

    protected $fillable = [
        'id_nguoiDung',
        'ten_nguoiNhan',
        'sdt_nhanHang',
        'ten_nha',
        'tinh',
        'huyen',
        'xa',
        'tongTien',
        'phuongThucThanhToan',
        'trangThaiDonHang',
        // 2 cột mới
        'ngay_du_kien_giao',
        'ngay_giao_thuc_te'
    ];

    protected $casts = [
        'ngay_du_kien_giao' => 'datetime',
        'ngay_giao_thuc_te' => 'datetime',
    ];
}
