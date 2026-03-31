<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'company_id', 'plan_id', 'status', 'billing_cycle',
        'starts_at', 'ends_at', 'trial_ends_at', 'amount_paid', 'payment_reference',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'trial_ends_at' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']) && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast() || $this->status === 'expired';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'trial' => 'blue',
            'expired' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
