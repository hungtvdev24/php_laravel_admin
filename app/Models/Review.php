<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $primaryKey = 'id_danhGia';

    protected $fillable = [
        'id_nguoiDung',
        'id_sanPham',
        'id_donHang',
        'variation_id', // Thêm variation_id
        'soSao',
        'binhLuan',
        'urlHinhAnh',
        'ngayDanhGia',
    ];

    protected $casts = [
        'soSao' => 'integer',
        'ngayDanhGia' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoiDung', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_sanPham', 'id_sanPham');
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_donHang', 'id_donHang');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id', 'id');
    }
}