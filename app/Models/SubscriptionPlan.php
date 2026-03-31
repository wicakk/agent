<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'max_users', 'max_stores', 'max_warehouses',
        'price_monthly', 'price_yearly', 'features', 'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function getPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->price_monthly, 0, ',', '.');
    }
}
