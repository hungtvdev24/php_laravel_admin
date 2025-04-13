<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mess extends Model
{
    use HasFactory;

    protected $table = 'mess'; // Tên bảng

    protected $fillable = [
        'sender_id',
        'sender_type',
        'receiver_id',
        'receiver_type',
        'content',
        'is_read',
    ];

    /**
     * Quan hệ đa hình với người gửi (có thể là User, Admin, hoặc Employee).
     */
    public function sender()
    {
        return $this->morphTo();
    }

    /**
     * Quan hệ đa hình với người nhận (có thể là User, Admin, hoặc Employee).
     */
    public function receiver()
    {
        return $this->morphTo();
    }
}