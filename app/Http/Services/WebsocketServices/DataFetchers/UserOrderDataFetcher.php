<?php

namespace App\Http\Services\WebsocketServices\DataFetchers;

use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Contracts\Repositories\TradeTransactionRepositoryInterface;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Services\TransactionService;
use App\Model\Buy;
use App\Model\Sell;

class UserOrderDataFetcher
{
    private BuyOrderRepository $buyOrderRepository;

    private SellOrderRepository $sellOrderRepository;

    public function __construct(
        private TransactionService $transactionService,
        private TradeTransactionRepositoryInterface $tradeTransactionRepository,
        private StopLimitRepositoryInterface $stopLimitRepository
    ) {
        $this->buyOrderRepository = new BuyOrderRepository(Buy::class);
        $this->sellOrderRepository = new SellOrderRepository(Sell::class);
    }

    public function fetchData(int $baseCoinId, int $tradeCoinId, int $userId): array
    {
        $tradeData = $this->tradeTransactionRepository->findByCoinIdsAndUserId(
            $baseCoinId,
            $tradeCoinId,
            $userId,
            40
        )->toArray();

        $buyOpenOrders = $this->buyOrderRepository->getMyOrders($baseCoinId, $tradeCoinId, $userId)->get()->toArray();
        $sellOpenOrders = $this->sellOrderRepository->getMyOrders($baseCoinId, $tradeCoinId, $userId)->get()->toArray();
        $allBuyOrders = $this->buyOrderRepository->getAllBuyOrders($baseCoinId, $tradeCoinId, $userId, 50)->toArray();
        $allSellOrders = $this->sellOrderRepository->getAllSellOrders($baseCoinId, $tradeCoinId, $userId, 50)->toArray();
        $allOpenOrders = array_merge($buyOpenOrders, $sellOpenOrders);

        usort($allOpenOrders, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime(datetime: $a['created_at']);
        });

        $data['transactions'] = $tradeData;
        $data['orders'] = $allOpenOrders;
        $data['buy_orders'] = $allBuyOrders;
        $data['sell_orders'] = $allSellOrders;

        $data['stop_limit_orders'] = $this->stopLimitRepository->findByCoinIdsAndUser($baseCoinId, $tradeCoinId, $userId);

        return $data;
    }
}
