<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyDepositPaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'payment_method',
        'status',
        'type'
    ];
}
