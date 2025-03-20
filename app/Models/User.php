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

    // Quan hệ nhiều-nhiều với Notification
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }
}