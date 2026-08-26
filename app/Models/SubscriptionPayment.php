<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'package_id',
        'seats',
        'promo_code_id',
        'period',
        'amount',
        'discount_amount',
        'kapitalbank_order_id',
        'kapitalbank_order_password',
        'status',
    ];

    protected $casts = [
        'seats'           => 'integer',
        'amount'          => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** The member who made the payment. */
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
