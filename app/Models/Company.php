<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo', 'phone', 'email',
        'address', 'city', 'province', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany        { return $this->hasMany(User::class); }
    public function branches(): HasMany     { return $this->hasMany(Branch::class); }
    public function warehouses(): HasMany   { return $this->hasMany(Warehouse::class); }
    public function products(): HasMany     { return $this->hasMany(Product::class); }
    public function stores(): HasMany       { return $this->hasMany(Store::class); }
    public function distributions(): HasMany{ return $this->hasMany(Distribution::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function subscriptions(): HasMany{ return $this->hasMany(Subscription::class); }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trial'])
            ->latest('id');
    }

    // Total sales across ALL branches
    public function salesCount(): int
    {
        return $this->users()
            ->where('role', 'sales')
            ->where('is_active', true)
            ->count();
    }

    public function canAddUser(): bool
    {
        $sub = $this->activeSubscription;
        if (!$sub) return false;
        return $this->salesCount() < $sub->plan->max_users;
    }
}
