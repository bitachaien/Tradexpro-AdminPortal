<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class StakingInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'staking_offer_id',
        'user_id',
        'coin_type',
        'period',
        'offer_percentage',
        'terms_type',
        'minimum_maturity_period',
        'auto_renew_status',
        'status	investment_amount',
        'earn_daily_bonus',
        'total_bonus',
        'auto_renew_from',
        'is_auto_renew'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
