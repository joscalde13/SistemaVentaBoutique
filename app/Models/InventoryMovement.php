<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'sale_id', 'type', 'quantity',
        'stock_before', 'stock_after', 'reason',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'entry' => 'Entrada',
            'withdrawal' => 'Salida',
            'sale' => 'Venta',
            'sale_return' => 'Devolución',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
