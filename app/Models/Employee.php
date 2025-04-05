<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees'; // Tên bảng
    protected $primaryKey = 'id_nhanVien'; // Khóa chính

    // Các trường có thể điền dữ liệu
    protected $fillable = [
        'tenNhanVien',
        'tuoi',
        'diaChi',
        'tenTaiKhoan',
        'matKhau',
        'trangThai',
        'id_admin',
    ];

    // Ẩn trường mật khẩu khi trả về dữ liệu
    protected $hidden = [
        'matKhau',
    ];

    // Quan hệ với model Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }
}