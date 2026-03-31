<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'company_id', 'product_id', 'warehouse_id', 'user_id',
        'type', 'quantity', 'stock_before', 'stock_after',
        'unit_price', 'reference_no', 'reason', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'in' => 'green',
            'out' => 'red',
            'adjustment' => 'yellow',
            'return' => 'blue',
            default => 'gray',
        };
    }
}
