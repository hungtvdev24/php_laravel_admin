<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteProduct extends Model
{
    protected $table = 'favorite_products';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // Quan hệ với người dùng (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với sản phẩm (Product)
    public function product()
    {
        // Đảm bảo khóa chính của bảng products là 'id_sanPham'
        return $this->belongsTo(Product::class, 'product_id', 'id_sanPham')
                    ->select(['id_sanPham', 'urlHinhAnh', 'thuongHieu', 'tenSanPham', 'gia']);
    }
}