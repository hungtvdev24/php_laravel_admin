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
}
