<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinPairApiPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        "pair",
        "buy_price",
        "buy_amount",
        "sell_price",
        "sell_amount",
    ];
}
