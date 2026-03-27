<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_title',
        'short_description',
        'description',
        'ingredients',
        'usage',
        'highlights',
        'price',
        'stock',
        'main_image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
}
