<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_value',
        'discount_type',
        'min_order_value',
        'max_discount',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Quan hệ với user_voucher_usage
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_voucher_usage')
                    ->withPivot('used_at', 'order_id')
                    ->withTimestamps();
    }

    // Quan hệ với DonHang
    public function orders()
    {
        return $this->hasMany(DonHang::class, 'voucher_id');
    }
}