<?php

namespace App\Http\Controllers\Api;

use App\Services\MarketOverViewServices\MarketOverviewService;
use Throwable;

class MarketController
{
    public function getMarketOverview(MarketOverviewService $marketOverviewService)
    {
        try {
            return response(
                responseData(
                    true,
                    'Successfully fetched',
                    $marketOverviewService->getMarketOverviewData())
            );
        } catch (Throwable $e) {
            storeException('MarketOverview', $e->getMessage());

            return response(responseData(false, __('Something went wrong')));
        }
    }
}
