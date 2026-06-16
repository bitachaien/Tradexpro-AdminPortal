<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiatWithdrawalCurrency extends Model
{
    use HasFactory;
    protected $fillable = ['currency_id', 'status'];
}
