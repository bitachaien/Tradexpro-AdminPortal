<?php

namespace App\Contracts\Repositories;

use App\Dtos\ClosebleOrderDto;

interface ClosableOrderRepository
{
    public function findByIdStatusUserIdAndLock(int $id, int $userId, int $status): ?ClosebleOrderDto;

    public function findByIdStatusAndUserId(int $id, int $userId, int $status): ?ClosebleOrderDto;

    public function closeOrder(int $id): void;
}
