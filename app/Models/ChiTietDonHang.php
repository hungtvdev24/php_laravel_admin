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
        'variation_id', // Thêm variation_id vào $fillable
        'soLuong',
        'gia'
    ];

    protected $casts = [
        'gia' => 'decimal:2',
    ];

    public function sanPham()
    {
        return $this->belongsTo(Product::class, 'id_sanPham', 'id_sanPham');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id', 'id');
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_donHang', 'id_donHang');
    }
}