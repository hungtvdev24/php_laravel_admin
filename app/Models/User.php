<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'tuoi'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'tuoi' => 'integer',
    ];

    public function gioHangs()
    {
        return $this->hasMany(GioHang::class, 'id_nguoiDung', 'id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_nguoiDung', 'id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Mess::class, 'sender_id', 'id')
                    ->where('sender_type', 'App\\Models\\User');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Mess::class, 'receiver_id', 'id')
                    ->where('receiver_type', 'App\\Models\\User');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_voucher_usage')
                    ->withPivot('used_at', 'order_id')
                    ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(DonHang::class, 'id_nguoiDung');
    }
}