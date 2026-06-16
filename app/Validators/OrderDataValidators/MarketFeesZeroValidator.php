<?php

namespace App\Validators\OrderDataValidators;

use App\Dtos\OrderValidatorDto;
use App\Exceptions\OrderException;
use Closure;

class MarketFeesZeroValidator
{
    public function handle(OrderValidatorDto $orderData, Closure $next)
    {
        $data = $orderData->data;
        if($data->is_market == 0)
        {
            return $next($orderData);    
        }

        $feesZero = isFeesZeroForMarket($data->user_id, $data->amount);

        if ($feesZero) {
            throw new OrderException(__('Minimum '.$orderData->type.' Amount Should Be ') . $feesZero);
        }

        return $next($orderData);
    }
}
