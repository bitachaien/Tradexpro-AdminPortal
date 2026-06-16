<?php

namespace App\Services\Order;

class OrderCostService
{
    public function getTotalCost(
        $price,
        $amount,
        $makerFees,
        $takerFees,
        string $type
    )
    {
        if($type == 'sell')
        {
            return custom_number_format($amount);
        }

        $total = bcmul($price, $amount);
        $fees = $makerFees > $takerFees ? $makerFees : $takerFees;
        $totalWithFees = bcadd($total, bcdiv(bcmul($total, $fees), "100"));

        return custom_number_format($totalWithFees);
    }
}
