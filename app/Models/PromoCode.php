<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'promoter_id',
        'discount_type',
        'discount_value',
        'commission_type',
        'commission_value',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'commission_value' => 'decimal:2',
        'expires_at'       => 'date',
        'is_active'        => 'boolean',
    ];

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_id');
    }

    public function redemptions()
    {
        return $this->hasMany(PromoRedemption::class);
    }

    /**
     * Kod hazırda istifadəyə yararlıdır? (aktiv, müddəti bitməyib, limit dolmayıb)
     */
    public function isUsable(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }
        return true;
    }

    /**
     * Verilmiş məbləğə görə endirim məbləğini (AZN) hesablayır.
     */
    public function discountFor(float $amount): float
    {
        $discount = $this->discount_type === 'percent'
            ? $amount * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(min($discount, $amount), 2);
    }

    /**
     * Faktiki ödənilən məbləğə görə komissiyanı (AZN) hesablayır.
     */
    public function commissionFor(float $paidAmount): float
    {
        $commission = $this->commission_type === 'percent'
            ? $paidAmount * ((float) $this->commission_value / 100)
            : (float) $this->commission_value;

        return round(min($commission, $paidAmount), 2);
    }
}
