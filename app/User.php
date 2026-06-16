<?php

namespace App;

use App\Model\Role;
use App\Model\AffiliationCode;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name', 'email','g2f_enabled', 'password','role','photo','phone','status',
        'is_verified','country_code','country','phone_verified','google2fa_secret','email_enabled','phone_enabled','otp_code','reset_code','gender', 'birth_date',
        'language', 'device_id', 'device_type', 'push_notification_status','role_id',
        'email_notification_status',
        'currency','last_seen','nickname',
        'is_default',
        'api_access_allow_user','trade_access_allow_user', 'withdrawal_access_allow_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->hasOne(AffiliationCode::class);
    }

    public function getFirstLastNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }
    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = strtoupper($value);
    }
    public function roles(){
        return $this->belongsTo(Role::class,'role_id','id');
    }
}
