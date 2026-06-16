<?php

namespace App\Http\Services\WebsocketServices\DataFetchers;

use App\Http\Services\CoinService;
use App\Http\Services\DashboardService;
use App\Http\Services\UserWalletService;


class AssetDataFetcherService
{
    public function __construct(
        private DashboardService $dashboardService,
        private CoinService $coinService,
        private UserWalletService $walletService
    ) {}

    public function fetchData(int $baseCoinId, int $tradeCoinId, int $userId)
    {

        $onOrderData = $this->dashboardService->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId);
        $baseCoinData = $this->dashboardService->getCoinPairForUser($baseCoinId, $tradeCoinId, $userId);
        $wallet = $this->walletService->getBalance($userId, $baseCoinData->parent_coin_id);

        $data['order_data']['base_coin'] = $this->coinService->getCoinTypeById($baseCoinId);
        $data['order_data']['trade_coin'] = $this->coinService->getCoinTypeById($tradeCoinId);
        $data['order_data']['on_order']['trade_wallet'] = $onOrderData['total_sell'];
        $data['order_data']['on_order']['base_wallet'] = $onOrderData['total_buy'];
        $data['order_data']['total']['trade_wallet']['balance'] = $baseCoinData->balance;
        $data['order_data']['total']['base_wallet']['balance'] = json_decode($wallet)->balance;

        $data['order_data']['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal;
        $data['order_data']['on_order']['trade_wallet_total'] = bcaddx($onOrderData['total_sell'], $baseCoinData->balance, 8);
        $data['order_data']['on_order']['base_wallet_total'] = bcaddx($onOrderData['total_buy'], $data['order_data']['total']['base_wallet']['balance'], 8);

        return $data;
    }
}
