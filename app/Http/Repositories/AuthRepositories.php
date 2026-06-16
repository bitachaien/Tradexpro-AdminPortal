<?php
namespace App\Http\Repositories;
use App\Http\Services\Logger;
use App\Model\Coin;
use App\Model\UserVerificationCode;
use App\Model\Wallet;
use App\User;

class AuthRepositories
{
    public function __construct()
    {
    }
    public function generate_email_verification_key()
    {
        $key = randomNumber(6);
        return $key;
    }

    public function create($userData)
    {
        try {
            return User::create($userData);
        } catch (\Exception $e) {
            storeException('user create', $e->getMessage());
            return false;
        }
    }

    public function createUserVerification($data)
    {
        try {
            return UserVerificationCode::create($data);
        } catch (\Exception $e) {
            storeException('user verification create', $e->getMessage());
            return false;
        }
    }
}
