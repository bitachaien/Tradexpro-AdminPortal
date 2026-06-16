<?php

namespace App\Services\MarketOverViewServices;

use App\Contracts\Repositories\MarketOverViewCoinPairRepositoryInterface;
use App\Model\CurrencyList;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketOverviewTopCoinListService
{
    const TYPE_TO_ORDER_BY_MAPPING = [
        1 => [
            'order_by' => 'price',
            'direction' => 'desc',
        ],

        2 => [
            'order_by' => 'price',
            'direction' => 'desc',
        ],

        3 => [
            'order_by' => 'price',
            'direction' => 'desc',
        ],

        4 => [
            'order_by' => 'id',
            'direction' => 'desc',
        ],
    ];

    public function __construct(
        private MarketOverViewCoinPairRepositoryInterface $coinPairRepository
    ) {}

    public function getTopCoinList(
        string $fiatCurrency,
        int $limit,
        int $offset,
        int $type,
        bool $isFutureTrade,
        ?string $filter = null
    ): LengthAwarePaginator {
        if (! array_key_exists($type, self::TYPE_TO_ORDER_BY_MAPPING)) {
            throw new \InvalidArgumentException('Invalid type '.$type);
        }

        $currencyDetails = CurrencyList::where(['code' => strtoupper($fiatCurrency)])->first();

        $result = $this->coinPairRepository->getTopCoinList(
            self::TYPE_TO_ORDER_BY_MAPPING[$type]['order_by'],
            self::TYPE_TO_ORDER_BY_MAPPING[$type]['direction'],
            $isFutureTrade,
            $limit,
            $offset,
            $filter
        );

        $result->map(function ($query) use ($currencyDetails) {
            $walletBalance = $query->total_balance;
            $query['total_balance'] = convertCoinPriceToFiatCurrency(($walletBalance * $query->price), $currencyDetails);
            $query->price = convertCoinPriceToFiatCurrency($query->price, $currencyDetails);
            $query->high = convertCoinPriceToFiatCurrency($query->high, $currencyDetails);
            $query->low = convertCoinPriceToFiatCurrency($query->low, $currencyDetails);
            if (isset($query->coin_icon)) {
                $query->coin_icon = createImageUrl(IMG_ICON_PATH, $query->coin_icon);
            }
        });

        return $result;
    }
}
