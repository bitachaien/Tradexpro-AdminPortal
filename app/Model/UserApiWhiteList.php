<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserApiWhiteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'status',
        'trade_access',
        'withdrawal_access',
        'number_of_request'
    ];
}
