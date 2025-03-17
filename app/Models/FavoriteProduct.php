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
        // Giả sử model sản phẩm của bạn là Product và khóa chính trong bảng products là 'id_sanPham'
        return $this->belongsTo(Product::class, 'product_id', 'id_sanPham');
    }
}
