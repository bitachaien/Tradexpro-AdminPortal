<?php

namespace App\Model;

use App\User;
use App\Model\Coin;
use Illuminate\Database\Eloquent\Model;

class FutureWallet extends Model
{
    protected $fillable = [
        'wallet_name',
        'user_id',
        'coin_id',
        'coin_type',
        'balance',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class,'coin_id');
    }
}
