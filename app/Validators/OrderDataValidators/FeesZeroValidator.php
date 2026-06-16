<?php

namespace App\Validators\OrderDataValidators;

use App\Dtos\OrderValidatorDto;
use App\Exceptions\OrderException;
use Closure;

class FeesZeroValidator
{
    public function handle(OrderValidatorDto $orderData, Closure $next)
    {
        $data = $orderData->data;
        $type = $orderData->type;
        if($data->is_market == 1)
        {
            return $next($orderData);    
        }

        $feesZero = isFeesZero($data->user_id, $data->base_coin_id, $data->trade_coin_id, $data->amount, $type, $data->price);

        if ($feesZero) {
            throw new OrderException(__('Minimum ' . $orderData->type . ' total should be ') . $feesZero);
        }

        return $next($orderData);
    }
}
