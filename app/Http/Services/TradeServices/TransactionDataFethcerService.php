<?php

namespace App\Http\Services\TradeServices;

use App\Dtos\CreateTransactionDto;
use App\Model\Buy;
use App\Model\Sell;

class TransactionDataFethcerService
{
    private const BOT_ORDER = 1;
    private const NOT_BOT_ORDER = 0;

    public function fetchTransactionData(Buy $buy, Sell $sell, $amount): CreateTransactionDto
    {
        $priceOrderType = $this->isSellOrder($buy, $sell) ? 'sell' : 'buy';
        $priceSource = $priceOrderType === 'sell' ? $sell : $buy;
        $btcRateSource = $priceOrderType === 'sell' ? $sell : $buy;

        $price = custom_number_format($priceSource->price);

        if (($buy->is_market == 1 && $sell->is_market == 0)) {
            $price = custom_number_format($sell->price);
        }

        if ($buy->is_market == 0 && $sell->is_market == 1) {
            $price = custom_number_format($buy->price);
        }

        if ($buy->is_market == 1 && $sell->is_market == 1) {
            $price = $priceOrderType == 'sell' ? $buy->price : $sell->price;
        }

        $btcRate = custom_number_format($btcRateSource->btc_rate);

        $buyFees = $this->calculateBuyFees($price, $amount, $buy, $priceOrderType);
        $sellFees = $this->calculateSellFees($price, $amount, $sell, $priceOrderType);

        return new CreateTransactionDto(
            null,
            $sell->base_coin_id,
            $sell->trade_coin_id,
            $buy->id,
            $sell->id,
            $buy->user_id,
            $sell->user_id,
            $priceOrderType,
            custom_number_format($amount),
            $price,
            $btcRate,
            bcmul($amount, $price),
            $buyFees,
            $sellFees,
            $this->determineBotOrder($buy, $sell),
            bcmul($amount, $btcRate)
        );
    }

    private function isSellOrder(Buy $buy, Sell $sell): bool
    {
        return strtotime($buy->created_at) > strtotime($sell->created_at);
    }

    private function calculateBuyFees($price, $amount, Buy $order, string $priceOrderType): string
    {
        $feeType = $priceOrderType === 'sell' ? 'taker_fees' : 'maker_fees';
        return bcdiv(bcmul($price, bcmul($amount, $order->$feeType)), 100);
    }

    private function calculateSellFees($price, $amount, Sell $order, string $priceOrderType): string
    {
        $feeType = $priceOrderType === 'sell' ? 'maker_fees' : 'taker_fees';
        return bcdiv(bcmul($price, bcmul($amount, $order->$feeType)), 100);
    }

    private function determineBotOrder(Buy $buy, Sell $sell): int
    {
        return ($buy->is_bot === self::BOT_ORDER || $sell->is_bot === self::BOT_ORDER) ? self::BOT_ORDER : self::NOT_BOT_ORDER;
    }
}
