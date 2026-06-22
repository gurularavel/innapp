<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoRedemption extends Model
{
    protected $fillable = [
        'promo_code_id',
        'promoter_id',
        'customer_id',
        'subscription_payment_id',
        'discount_applied',
        'commission_amount',
        'status',
        'available_at',
        'payout_id',
    ];

    protected $casts = [
        'discount_applied'  => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'available_at'      => 'datetime',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payment()
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    public function payout()
    {
        return $this->belongsTo(PromoterPayout::class, 'payout_id');
    }
}
