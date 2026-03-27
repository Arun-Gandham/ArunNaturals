<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'target_audience',
        'target_value',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'target_value'     => 'decimal:2',
        'starts_at'        => 'datetime',
        'expires_at'       => 'datetime',
        'is_active'        => 'boolean',
    ];

    public function exclusions(): HasMany
    {
        return $this->hasMany(CouponExclusion::class);
    }

    public function isAllowedForPhone(?string $phone): bool
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return false;
        }

        return !$this->exclusions()
            ->where('customer_phone', $phone)
            ->exists();
    }
}
