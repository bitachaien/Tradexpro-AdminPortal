<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinPair extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'parent_coin_id',
        'child_coin_id',
        'value',
        'volume',
        'change',
        'high',
        'low',
        'status',
        'initial_price',
        'price',
        'is_chart_updated',
        'bot_trading',
        'bot_trading_sell',
        'bot_trading_buy',
        'is_token',
        'bot_possible',
        'enable_future_trade',
        'maintenance_margin_rate',
        'leverage_fee',
        'leverage',
        'max_leverage',
        'minimum_amount_future',
        'is_default',
        'pair_decimal',
        
        'bot_operation',
        'bot_percentage',
        'upper_threshold',
        'lower_threshold'

    ];

    public function parent_coin()
    {
        return $this->belongsTo(Coin::class,'parent_coin_id');
    }
    public function child_coin()
    {
        return $this->belongsTo(Coin::class,'child_coin_id');
    }
}
