<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FutureTradeTransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'future_wallet_id',
        'coin_pair_id',
        'type',
        'amount',
        'coin_type',
        'symbol',
        'order_id'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function coin_pair_details()
    {
        return $this->hasMany(CoinPair::class,'id', 'coin_pair_id');
    }
}
