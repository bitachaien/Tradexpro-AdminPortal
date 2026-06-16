<?php

namespace App\Contracts\Repositories;

interface WalletAddressHistoryRepositoryInterface
{
    public function getNetWorkFromWalletAddress(string $address): ?int;
}
