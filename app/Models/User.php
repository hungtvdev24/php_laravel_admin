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
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_nguoiDung', 'id');
    }

    // Quan hệ 1-nhiều: Tin nhắn đã gửi
    public function sentMessages()
    {
        return $this->hasMany(Mess::class, 'sender_id', 'id');
    }

    // Quan hệ 1-nhiều: Tin nhắn đã nhận
    public function receivedMessages()
    {
        return $this->hasMany(Mess::class, 'receiver_id', 'id');
    }
}