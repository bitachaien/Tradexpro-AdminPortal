<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StakingOffer extends Model
{
    use HasFactory;

    public function staking_investment()
    {
        return $this->hasMany(StakingInvestment::class,'staking_offer_id');
    }
}
