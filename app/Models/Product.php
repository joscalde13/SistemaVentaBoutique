<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'category_id', 'name', 'description', 'size', 'color',
        'purchase_price', 'sale_price', 'max_discount', 'stock', 'image_path',
        'received_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'received_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'Inactiva';
        }

        return $this->stock > 0 ? 'Disponible' : 'Agotada';
    }
}
