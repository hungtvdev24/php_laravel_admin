<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $table = 'product_variations';

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Xóa $attributes['size'] để đồng bộ với logic null trong ProductController

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_sanPham');
    }

    public function images()
    {
        return $this->hasMany(ProductVariationImage::class, 'product_variation_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(ChiTietDonHang::class, 'variation_id', 'id');
    }

    public function mucGioHangs()
    {
        return $this->hasMany(MucGioHang::class, 'variation_id', 'id');
    }
}