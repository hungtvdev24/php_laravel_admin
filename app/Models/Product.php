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
        'gia', // Giữ lại nhưng không bắt buộc
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

    // Quan hệ với danh mục
    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'id_danhMuc', 'id_danhMuc');
    }

    // Quan hệ với biến thể sản phẩm
    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'id_sanPham');
    }

    // Quan hệ với chi tiết đơn hàng
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_sanPham', 'id_sanPham');
    }

    // Quan hệ với đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_sanPham', 'id_sanPham');
    }

    // Quan hệ với mục giỏ hàng
    public function mucGioHangs()
    {
        return $this->hasMany(MucGioHang::class, 'id_sanPham', 'id_sanPham');
    }

    // Cập nhật số lượng bán
    public function capNhatSoLuongBan()
    {
        $this->soLuongBan = $this->orderDetails()->whereHas('order', function ($query) {
            $query->where('trangThai', 'completed');
        })->sum('soLuong');
        $this->save();
    }

    // Cập nhật số sao đánh giá
    public function capNhatSoSaoDanhGia()
    {
        $this->soSaoDanhGia = $this->reviews()->avg('soSao') ?? 0;
        $this->save();
    }
}