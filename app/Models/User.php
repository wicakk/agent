<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'email', 'phone', 'password',
        'role', 'avatar', 'is_active', 'latitude', 'longitude',
        'last_location_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'password'           => 'hashed',
        'is_active'          => 'boolean',
        'latitude'           => 'decimal:8',
        'longitude'          => 'decimal:8',
        'last_location_at'   => 'datetime',
        'last_login_at'      => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo  { return $this->belongsTo(Branch::class); }

    public function salesActivities(): HasMany { return $this->hasMany(SalesActivity::class); }
    public function transactions(): HasMany    { return $this->hasMany(Transaction::class); }
    public function gpsLogs(): HasMany         { return $this->hasMany(GpsLog::class); }
    public function assignedStores(): HasMany  { return $this->hasMany(Store::class); }
    public function stockMovements(): HasMany  { return $this->hasMany(StockMovement::class); }

    // Role helpers
    public function isOwner(): bool         { return $this->role === 'owner'; }
    public function isAdmin(): bool         { return $this->role === 'admin'; }
    public function isSales(): bool         { return $this->role === 'sales'; }
    public function isAdminOrOwner(): bool  { return in_array($this->role, ['owner', 'admin']); }

    // Branch scope helper:
    // Owner sees ALL data across all branches.
    // Admin sees only their branch's data.
    // Sales sees only their own assigned data within their branch.
    public function branchScopeId(): ?int
    {
        if ($this->isOwner()) return null; // null = no filter = see all
        return $this->branch_id;
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'owner' => 'purple',
            'admin' => 'blue',
            'sales' => 'green',
            default => 'gray',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) return asset('storage/' . $this->avatar);
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=6366f1&background=e0e7ff&bold=true";
    }
}
