<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariationImage extends Model
{
    use HasFactory;

    protected $table = 'product_variation_images';

    protected $fillable = [
        'product_variation_id',
        'image_url',
    ];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id', 'id');
    }
}