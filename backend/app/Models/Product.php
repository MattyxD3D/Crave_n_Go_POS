<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image_path',
        'stock_qty',
        'low_stock_threshold',
        'is_available',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'stock_qty' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function addOns()
    {
        return $this->belongsToMany(AddOn::class, 'product_add_ons', 'product_id', 'add_on_id');
    }

    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'combo_items', 'product_id', 'combo_id')
                    ->withPivot('quantity');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }
}
