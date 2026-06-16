<?php

namespace App\Http\Services\TradeServices;

use App\Contracts\Repositories\FindableByUserIdAndCoinId;
use App\Contracts\Repositories\TradableOrderRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class FeeCheckerAndRefundService
{
    const STATUS_PROCESSED = 1;

    public function __construct(
        private FindableByUserIdAndCoinId $walletRepository
    ) {
    }

    public function shouldProcessOrder(
        TradableOrderRepositoryInterface $repository,
        int $orderId,
        string $type,
        $price,
    ): bool {
        DB::beginTransaction();
        try {
            $order = $repository->findByIdAndLock($orderId);
            if ($order === null || $order->status == self::STATUS_PROCESSED) {
                DB::rollBack();
                return false;
            }

            $amount = custom_number_format(bcsub($order->amount, $order->processed));
            $buyFees = $order->taker_fees != 0 ? bcdiv(bcmul($price, bcmul($amount, $order->taker_fees)), 100) : 1;
            $sellFees = $order->maker_fees != 0 ? bcdiv(bcmul($price, bcmul($amount, $order->taker_fees)), 100) : 1;

            if (!(bccomp($buyFees, "0") === 0 || bccomp($sellFees, "0") === 0)) {
                DB::rollBack();
                return true;
            }

            $this->refund(
                $repository,
                $orderId,
                $type == 'buy' ? $order->base_coin_id : $order->trade_coin_id,
                $type == 'buy' ? bcadd(bcmul($price, $amount), 0) : $amount,
                $order->procssed
            );

            DB::commit();

            return false;
        } catch (Exception $exception) {
            DB::rollBack();
            storeException('FeeChecker', $exception);
            return false;
        }
    }

    private function refund(
        TradableOrderRepositoryInterface $repository,
        int $orderId,
        int $coinId,
        $adjustValue,
        $processedAmount
    ): void {
        storeBotException('OrderProcessing', "Return for fees 0");

        $wallet = $this->walletRepository->findByUserIdAndCoinId($orderId, $coinId);
        $wallet->increment('balance', $adjustValue);
        $repository->closeOrder($orderId);
    }

}
