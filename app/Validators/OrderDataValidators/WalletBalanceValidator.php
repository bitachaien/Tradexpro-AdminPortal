<?php

namespace App\Validators\OrderDataValidators;

use App\Dtos\OrderValidatorDto;
use App\Exceptions\OrderException;
use App\Http\Repositories\UserWalletRepository;
use App\Services\Order\OrderCostService;
use Closure;

class WalletBalanceValidator
{
    public function __construct(
        private UserWalletRepository $walletRepository,
        private OrderCostService $orderCostService
    ) 
    {}

    public function handle(OrderValidatorDto $orderData, Closure $next)
    {
        $data = $orderData->data;
        $type = $orderData->type;

        $walletDetails = $this->walletRepository->getUserSingleWalletBalance(
            $data->user_id,
            $type == 'buy'? $data->base_coin_id : $data->trade_coin_id
        );

        if (!$walletDetails) {
            throw new OrderException(__('Invalid '. $type.' order request!'));
        }

        $mainBalance = $walletDetails->balance;
        $totalCost = $this->orderCostService->getTotalCost(
            $data->price,
            $data->amount,
            $data->maker_fees,
            $data->taker_fees,
            $type
        );

        $mainBalance = $walletDetails->balance;

        $mainBalance = $walletDetails->balance;
        $totalCost = $this->orderCostService->getTotalCost(
            $data->price,
            $data->amount,
            $data->maker_fees,
            $data->taker_fees,
            $type
        );

        if (bccomp($mainBalance, $totalCost) === -1) {
            throw new OrderException(__('You need minimum balance(including fees): ') . $totalCost . ' ' . $walletDetails->coin_type);
        }

        return $next($orderData);
    }
}
