<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\WalletAddressHistoryRepositoryInterface;
use App\Model\WalletAddressHistory;

class WalletAddressHistoryRepository implements WalletAddressHistoryRepositoryInterface
{
    public function __construct(
        private WalletAddressHistory $model
    ) {}

    public function getNetWorkFromWalletAddress(string $address): ?int
    {
        $walletAddressHistory = $this->model->join('coins', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->where('wallet_address_histories.address', $address)
            ->select('coins.network')
            ->first();

        return $walletAddressHistory ? $walletAddressHistory->network : null;
    }
}
