<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    protected $table = 'danhMuc';
    protected $primaryKey = 'id_danhMuc';
    protected $fillable = ['tenDanhMuc', 'moTa'];
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    public function sanPhams()
    {
        return $this->hasMany(Product::class, 'id_danhMuc', 'id_danhMuc');
    }
}