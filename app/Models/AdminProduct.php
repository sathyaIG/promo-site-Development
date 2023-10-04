<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminProduct extends Model
{
    use HasFactory;
    protected $table = 'admin_product_list';
    protected $fillable = [
        'source_sku_id',
        'brand_slug',
        'sku_description',
        'top_slug',
        'mid_slug',
        'leaf_slug',
        'product_group',
        'parent_product_group',
        'sku_department',
        'source_sku_manufacturer_id',
        'sku_manufacturer_name',
        'flag',
        'created_at',
        'trash',
        'updated_at'
        // Add other fillable attributes here
    ];
}
