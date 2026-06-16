<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeReferralHistory extends Model
{
    use HasFactory;

    protected $fillable = ['trade_by','child_id','user_id','amount','percentage_amount','transaction_id','level','coin_type','wallet_id'];

    public function transactionDetails()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
