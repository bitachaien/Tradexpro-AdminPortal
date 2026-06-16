<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletNetwork extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_id',
        'coin_id',
        'address',
        'network_type',
        'status',
    ];
    public function wallet(){
        return $this->hasOne(Wallet::class,'id','wallet_id');
    }
}
