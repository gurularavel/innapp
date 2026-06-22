<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'doctor_id',
        'package_id',
        'promo_code_id',
        'period',
        'amount',
        'discount_amount',
        'kapitalbank_order_id',
        'kapitalbank_order_password',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
