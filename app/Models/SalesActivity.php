<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'store_id', 'type',
        'latitude', 'longitude', 'is_mock_location', 'accuracy',
        'photo', 'notes', 'activity_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_mock_location' => 'boolean',
        'activity_at' => 'datetime',
        'accuracy' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class, 'activity_id'); }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'check_in' => 'green',
            'check_out' => 'red',
            'order' => 'blue',
            'payment_collection' => 'purple',
            'survey' => 'yellow',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'check_in' => 'Check In',
            'check_out' => 'Check Out',
            'order' => 'Pesanan',
            'payment_collection' => 'Tagihan',
            'survey' => 'Survey',
            default => $this->type,
        };
    }
}
