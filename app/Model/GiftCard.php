<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid','coin_type','wallet_type','user_id','redeem_code','amount','note','owner_id','is_ads_created','status','gift_card_banner_id','lock','fees'
    ];

    public function user()
    {
        return $this->hasOne(User::class,'id', 'user_id');
    }

    public function banner(){
        return $this->hasOne(GiftCardBanner::class, 'uid', 'gift_card_banner_id');
    }

}
