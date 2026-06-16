<?php

namespace App\Services\MarketOverViewServices;

use App\Contracts\Repositories\MarketOverViewCoinPairRepositoryInterface;
use Illuminate\Support\Collection;

class MarketOverviewService
{
    const KEY_TO_ORDER_BY_MAP = [
        'highlight_coin' => [
            'order_by' => 'updated_at',
            'order_by_direction' => 'desc'
        ],
        'new_listing' => [
            'order_by' => 'created_at',
            'order_by_direction' => 'desc'
        ],
        'top_gainers' => [
            'order_by' => 'change',
            'order_by_direction' => 'desc'
        ]
    ];

    public function __construct(
        private MarketOverViewCoinPairRepositoryInterface $coinPairRepository,
    ) {}

    public function getMarketOverviewData(): array
    {
        $result = [];

        foreach (self::KEY_TO_ORDER_BY_MAP as $key => $value) {
            $coinPairs = $this->coinPairRepository->getCoinPairs(
                10, 
                $value['order_by'],
                $value['order_by_direction']
            );

            $result[$key] = $this->formattedData($coinPairs);
        }

        return $result;
    }

    private function formattedData(Collection $data): array 
    {
        $result = [];

        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'coin_icon' => createImageUrl(IMG_ICON_PATH, $item->child_coin->coin_icon),
                'price' => $item->coin_type,
                'usdt_price' => $item->price,
                'change' => $item->change,
                'currency_symbol' => $item->child_coin->coin_type .'/'. $item->parent_coin->coin_type,
            ];
        }

        return $result;
    }
}
