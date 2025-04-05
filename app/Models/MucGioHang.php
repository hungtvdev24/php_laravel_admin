<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MucGioHang extends Model
{
    protected $table = 'mucGioHang';
    protected $primaryKey = 'id_mucGioHang';
    public $timestamps = true;

    protected $fillable = [
        'id_gioHang',
        'id_sanPham',
        'variation_id', // Thêm để hỗ trợ biến thể
        'soLuong',
        'gia',
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class, 'id_gioHang', 'id_gioHang');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_sanPham', 'id_sanPham');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id', 'id');
    }
}