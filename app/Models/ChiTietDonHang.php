<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    use HasFactory;

    protected $table = 'chiTietDonHang';
    protected $primaryKey = 'id_chiTietDonHang';

    protected $fillable = [
        'id_donHang',
        'id_sanPham',
        'soLuong',
        'gia'
    ];

    // Quan hệ belongsTo với model Product (chỉnh sửa theo tên model của bạn)
    public function sanPham()
    {
        return $this->belongsTo(Product::class, 'id_sanPham', 'id_sanPham');
    }
}
