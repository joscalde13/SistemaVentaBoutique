<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'category_id', 'product_code', 'product_name', 'category_name',
        'quantity', 'unit_purchase_price', 'unit_sale_price', 'unit_discount',
        'max_discount_snapshot', 'line_total', 'line_cost', 'line_profit',
    ];

    protected function casts(): array
    {
        return [
            'unit_purchase_price' => 'decimal:2',
            'unit_sale_price' => 'decimal:2',
            'unit_discount' => 'decimal:2',
            'max_discount_snapshot' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_cost' => 'decimal:2',
            'line_profit' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
