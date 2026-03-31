<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'store_id', 'activity_id',
        'invoice_no', 'type', 'status', 'subtotal', 'discount',
        'tax', 'total', 'payment_method', 'due_date', 'paid_at', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax'      => 'decimal:2',
        'total'    => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function store(): BelongsTo   { return $this->belongsTo(Store::class); }
    public function activity(): BelongsTo { return $this->belongsTo(SalesActivity::class, 'activity_id'); }
    public function items(): HasMany     { return $this->hasMany(TransactionItem::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray', 'confirmed' => 'blue', 'delivered' => 'indigo',
            'paid'  => 'green', 'cancelled' => 'red', default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft', 'confirmed' => 'Dikonfirmasi',
            'delivered' => 'Terkirim', 'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan', default => $this->status,
        };
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($t) {
            if (!$t->invoice_no) {
                $t->invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
