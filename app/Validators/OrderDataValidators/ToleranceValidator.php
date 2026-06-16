<?php

namespace App\Validators\OrderDataValidators;

use App\Dtos\OrderValidatorDto;
use App\Services\Order\ToleranceCheckerService;
use Closure;

class ToleranceValidator
{
    public function __construct(
        private ToleranceCheckerService $toleranceCheckerService
    ) {}

    public function handle(OrderValidatorDto $orderData, Closure $next)
    {
        $data = $orderData->data;
        $coinPair = $orderData->coinPair;
        $type = $orderData->type;

        if($data->is_market == 1)
        {
            return $next($orderData);    
        }
        
        $this->toleranceCheckerService->checkTolarence($data, $coinPair, $type);

        return $next($orderData);
    }
}
