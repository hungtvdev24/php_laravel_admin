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
        'ngay_du_kien_giao',
        'ngay_giao_thuc_te'
    ];

    protected $casts = [
        'ngay_du_kien_giao' => 'datetime',
        'ngay_giao_thuc_te' => 'datetime',
    ];

    // Quan hệ hasMany với ChiTietDonHang
    public function chiTietDonHang()
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_donHang', 'id_donHang');
    }

    // Quan hệ belongsTo với User (nếu cần)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoiDung', 'id');
    }
}
