<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'name', 'code', 'owner_name', 'phone',
        'address', 'city', 'district', 'latitude', 'longitude',
        'status', 'store_type', 'last_visited_at', 'notes', 'photo',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_visited_at' => 'date',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function salesUser(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    public function activities(): HasMany { return $this->hasMany(SalesActivity::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function distributions(): HasMany { return $this->hasMany(Distribution::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'potential' => 'yellow',
            'inactive' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Aktif',
            'potential' => 'Potensial',
            'inactive' => 'Tidak Aktif',
            default => $this->status,
        };
    }
}
