<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'name',
        'coin_type',
        'currency_type',
        'currency_id',
        'network',
        'status',
        'is_withdrawal',
        'is_deposit',
        'is_buy',
        'is_sell',
        'coin_icon',
        'is_base',
        'is_currency',
        'is_primary',
        'is_wallet',
        'is_demo_trade',
        'is_transferable',
        'is_virtual_amount',
        'trade_status',
        'sign',
        'minimum_buy_amount',
        'minimum_sell_amount',
        'minimum_withdrawal',
        'maximum_withdrawal',
        'maximum_buy_amount',
        'maximum_sell_amount',
        'max_send_limit',
        'withdrawal_fees',
        'withdrawal_fees_type',
        'coin_price',
        'admin_approval',
        'ico_id',
        'is_listed',
        'last_block_number',
        'last_timestamp',
        'to_block_number',
        'from_block_number',
    ];

    public function setCoinTypeAttribute($value)
    {
        $this->attributes['coin_type'] = strtoupper($value);
    }

    public function coin_pair_usdt()
    {
       return $this->belongsTo(CoinPair::class,'id','child_coin_id');
    }
}
