<?php

namespace App\Services\TradingBotServices;

use App\Http\Repositories\Factories\OrderRepositoryFactory;
use App\Http\Repositories\TransactionRepository;

class ProcessedBotOrderRemoverService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private OrderRepositoryFactory $orderRepositoryFactory
    ) {}

    public function cleanBotOrders(
        int $baseCoinId,
        int $tradeCoinId,
        int $superAdminId,
        int $minTransactionsToKeep = 20
    )
    {
        $idsToKeep = $this->transactionRepository->getLatestTransactionIds(
            $baseCoinId,
            $tradeCoinId,
            $minTransactionsToKeep
        );

        $lastTransactionIdBefore24Hours = $this->transactionRepository->getLastTransactionIdBeforeHours(
            $baseCoinId,
            $tradeCoinId,
            24
        );

        if ($idsToKeep && count($idsToKeep) && count($lastTransactionIdBefore24Hours)) {
            $idsToKeep[] = $lastTransactionIdBefore24Hours[0];

            $this->transactionRepository->removeBotTransactions(
                $idsToKeep,
                max($idsToKeep),
                $baseCoinId,
                $tradeCoinId,
                $superAdminId
            );
        }

        foreach (['buy', 'sell'] as $type) {
            $this->orderRepositoryFactory->getOrderRepositoryByType($type)
                ->deleteBotOrders($baseCoinId, $tradeCoinId, $superAdminId);
        }

    }
}
