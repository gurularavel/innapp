<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoterPayout extends Model
{
    protected $fillable = [
        'promoter_id',
        'amount',
        'status',
        'method',
        'note',
        'requested_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'requested_at' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_id');
    }

    public function redemptions()
    {
        return $this->hasMany(PromoRedemption::class, 'payout_id');
    }
}
