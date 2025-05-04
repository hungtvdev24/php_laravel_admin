<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Authenticatable
{
    protected $table = 'employees';
    protected $primaryKey = 'id_nhanVien';
    protected $fillable = ['tenNhanVien', 'tuoi', 'diaChi', 'tenTaiKhoan', 'matKhau', 'trangThai', 'id_admin'];
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    // Ánh xạ trường mật khẩu thành matKhau
    public function getAuthPassword()
    {
        return $this->matKhau;
    }

    // Ánh xạ trường định danh thành id_nhanVien
    public function getAuthIdentifierName()
    {
        return 'id_nhanVien';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id');
    }
}