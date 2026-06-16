<?php

namespace App\Contracts\Repositories;

use App\Dtos\BotCoinPairDto;
use Illuminate\Support\Collection;

interface BotCoinPairRepositoryInterface
{
    public function getBotStatusActivePairs(): Collection;

    public function getSingleCoinPairById(int $id): ?BotCoinPairDto;
}
