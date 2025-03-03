<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'gioHang';
    protected $primaryKey = 'id_gioHang';
    public $timestamps = true;

    protected $fillable = [
        'id_nguoiDung',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoiDung', 'id');
    }

    public function mucGioHangs()
    {
        return $this->hasMany(MucGioHang::class, 'id_gioHang', 'id_gioHang');
    }
}