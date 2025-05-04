<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucherUsage extends Model
{
    use HasFactory;

    protected $table = 'user_voucher_usage'; // Chỉ định rõ tên bảng

    protected $fillable = ['user_id', 'voucher_id', 'used_at', 'order_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order()
    {
        return $this->belongsTo(DonHang::class, 'order_id', 'id_donHang');
    }
}