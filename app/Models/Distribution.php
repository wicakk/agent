<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'warehouse_id', 'driver_id', 'store_id',
        'delivery_no', 'status', 'destination_address',
        'destination_lat', 'destination_lng', 'notes',
        'scheduled_at', 'departed_at', 'delivered_at',
        'proof_photo', 'delivery_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'departed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'destination_lat' => 'decimal:8',
        'destination_lng' => 'decimal:8',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function driver(): BelongsTo { return $this->belongsTo(User::class, 'driver_id'); }
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function items(): HasMany { return $this->hasMany(DistributionItem::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_transit' => 'blue',
            'delivered' => 'green',
            'cancelled' => 'red',
            'returned' => 'orange',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'in_transit' => 'Dalam Perjalanan',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            'returned' => 'Dikembalikan',
            default => $this->status,
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => 'clock',
            'in_transit' => 'truck',
            'delivered' => 'check-circle',
            'cancelled' => 'x-circle',
            'returned' => 'arrow-uturn-left',
            default => 'question-mark-circle',
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity_requested');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($dist) {
            if (!$dist->delivery_no) {
                $dist->delivery_no = 'SJ-' . strtoupper(uniqid());
            }
        });
    }
}
