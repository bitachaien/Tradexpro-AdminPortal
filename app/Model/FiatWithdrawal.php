<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiatWithdrawal extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bank_id',
        'wallet_id',
        'admin_id',
        'coin_amount',
        'currency_amount',
        'rate',
        'currency',
        'fees',
        'status',
        'bank_slip',
        'payment_info'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
    public function bank()
    {
        return $this->belongsTo(UserBank::class,'bank_id');
    }
}
