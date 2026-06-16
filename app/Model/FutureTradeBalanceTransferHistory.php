<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;

class FutureTradeBalanceTransferHistory extends Model
{
    protected $fillable = [
        'user_id',
        'spot_wallet_id',
        'future_wallet_id',
        'amount',
        'transfer_from'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function spot_wallet()
    {
        return $this->belongsTo(Wallet::class, 'spot_wallet_id');
    }

    public function future_wallet()
    {
        return $this->belongsTo(FutureWallet::class, 'future_wallet_id');
    }
}
