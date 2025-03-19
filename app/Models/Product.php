<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id_sanPham';

    protected $fillable = [
        'tenSanPham',
        'thuongHieu',
        'gia',
        'urlHinhAnh',
        'moTa',
        'trangThai',
        'id_danhMuc',
        'soLuongBan',
        'soSaoDanhGia'
    ];

    protected $casts = [
        'gia' => 'decimal:2',
        'soSaoDanhGia' => 'float',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'id_danhMuc', 'id_danhMuc');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_sanPham', 'id_sanPham');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_sanPham', 'id_sanPham');
    }

    public function mucGioHangs()
    {
        return $this->hasMany(MucGioHang::class, 'id_sanPham', 'id_sanPham');
    }

    public function capNhatSoLuongBan()
    {
        $this->soLuongBan = $this->orderDetails()->whereHas('order', function ($query) {
            $query->where('trangThai', 'completed');
        })->sum('soLuong');

        $this->save();
    }

    public function capNhatSoSaoDanhGia()
    {
        $this->soSaoDanhGia = $this->reviews()->avg('soSao') ?? 0;
        $this->save();
    }
}