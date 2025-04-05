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

    public function chiTietDonHang()
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_donHang', 'id_donHang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoiDung', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_donHang', 'id_donHang');
    }

    // Quan hệ với lịch sử trạng thái
    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class, 'id_donHang', 'id_donHang')->orderBy('ngayThayDoi', 'asc');
    }
}