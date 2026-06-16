<?php

namespace App\Model;

use App\User;
use App\Model\Coin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FutureTradeLongShort extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'side',
        'user_id',
        'base_coin_id',
        'trade_coin_id',
        'parent_id',
        'entry_price',
        'exist_price',
        'price',
        'avg_close_price',
        'pnl',
        'amount_in_base_coin',
        'amount_in_trade_coin',
        'take_profit_price',
        'stop_loss_price',
        'liquidation_price',
        'margin',
        'fees',
        'comission',
        'executed_amount',
        'leverage',
        'margin_mode',
        'trade_type',
        'is_position',
        'future_trade_time',
        'closed_time',
        'status',
        'is_market',
        'trigger_condition',
        'current_market_price',
        'order_type',
        'stop_price'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function base_coin()
    {
        return $this->hasOne(Coin::class, 'id', 'base_coin_id');
    }

    public function children()
    {
        return $this->hasMany(FutureTradeLongShort::class, 'parent_id');
    }
}
