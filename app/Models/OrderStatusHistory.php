<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';
    protected $fillable = ['id_donHang', 'trangThaiDonHang', 'ngayThayDoi', 'ghiChu'];

    // Khai báo casts để tự động chuyển đổi ngayThayDoi thành Carbon
    protected $casts = [
        'ngayThayDoi' => 'datetime',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_donHang', 'id_donHang');
    }
}