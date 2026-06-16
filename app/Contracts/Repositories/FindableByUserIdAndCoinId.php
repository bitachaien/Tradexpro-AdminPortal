<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface FindableByUserIdAndCoinId
{
    public function findByUserIdAndCoinId(int $userId, int $coinId): Model;
}
