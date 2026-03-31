<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'code', 'address', 'city',
        'latitude', 'longitude', 'pic_name', 'pic_phone', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->products()->sum('stock_current');
    }

    public function getLowStockCountAttribute(): int
    {
        return $this->products()->whereColumn('stock_current', '<=', 'stock_minimum')->count();
    }
}
