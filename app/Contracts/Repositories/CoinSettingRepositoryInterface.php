<?php

namespace App\Contracts\Repositories;

use App\Model\CoinSetting;

interface CoinSettingRepositoryInterface
{
    public function getCoinSettingData(int $coinId,int $network);
    public function createCoinSetting(int $coinId,int $network): CoinSetting;
    public function getContractAddressFromWalletAddress(string $address): ?string;
    public function getNetworkFromContractAddress(string $contractAddress): ?int;
}
