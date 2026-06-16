<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','account_holder_name','account_holder_address', 'bank_name', 'bank_address', 'country', 'swift_code', 'iban', 'note','status'];
}
