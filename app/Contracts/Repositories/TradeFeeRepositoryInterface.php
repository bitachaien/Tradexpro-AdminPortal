<?php

namespace App\Contracts\Repositories;

use App\Dtos\TradeFeeCreationDto;
use App\Dtos\TradeFeeUpdateDto;
use App\TradeFee;
use Illuminate\Database\Eloquent\Builder;

interface TradeFeeRepositoryInterface
{
    public function getQuery(): Builder;

    public function create(TradeFeeCreationDto $data): TradeFee;

    public function changeStatusById(int $id);

    public function insert(array $data);

    public function update(int $id, TradeFeeUpdateDto $data);

    public function findById(int $id): ?TradeFee;

    public function countByUserIdAndCoinPairIds(array $coinPairIds, ?int $userId = null): int;
}
