<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLogin extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'userID',
        'login_type',
        'email',
        'access_token'
    ];       
}