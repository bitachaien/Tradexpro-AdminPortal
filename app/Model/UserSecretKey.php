<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSecretKey extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'secret_key',
        'start_date',
        'expire_date',
        'status',
        'number_of_request',
        'target_request',
        'trade_access',
        'withdrawal_access'
    ];
}
