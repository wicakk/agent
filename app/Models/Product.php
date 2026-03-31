<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'warehouse_id', 'name', 'sku', 'barcode',
        'category', 'brand', 'unit', 'unit_per_pack', 'stock_current',
        'stock_minimum', 'buy_price', 'sell_price', 'image', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock_current' => 'integer',
        'stock_minimum' => 'integer',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function distributionItems(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_current <= $this->stock_minimum;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_current <= 0) return 'empty';
        if ($this->stock_current <= $this->stock_minimum) return 'low';
        return 'normal';
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) return asset('storage/' . $this->image);
        return asset('images/product-placeholder.png');
    }
}
