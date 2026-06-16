<?php

namespace App\Services\TradingBotServices;

use App\Cache\BotCoinPairCache;
use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Http\Services\CacheService;
use App\Jobs\BotOrderJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class BotOrderService
{
    public function __construct(
        private BotCoinPairRepositoryInterface $coinPairRepository,
        private CacheService $cacheService,
        private BotCoinPairCache $botCoinPairCache
    ) {}

    public function getCoinPairsAndProcess(int $adminId)
    {
        while (true) {
            if (
                allsetting('enable_bot_trade') != STATUS_ACTIVE
                || Queue::size('trade-processor') > config('bot.maximum_allowed_queue_size')
            ) {
                sleep(30);

                continue;
            }

            $coinPairs = $this->botCoinPairCache->getBotStatusActivePairs();
            $intervalInSec = intval(settings('trading_bot_buy_interval') ?? 5);

            foreach ($coinPairs as $coinPair) {
                $startTime = $this->cacheService->get('bot_order_place_time_for_coin_pair_'.$coinPair->id);

                if ($startTime && ! $this->checkBotOrderPlacingInterval($startTime, $intervalInSec)) {
                    continue;
                }

                if (
                    ! $this->cacheService->get('bot_order_status_'.$coinPair->id)
                ) {
                    $this->cacheService->set('bot_order_status_'.$coinPair->id, 'processing');
                    BotOrderJob::dispatch($coinPair, $adminId)->onQueue('bot-order');
                }
            }

        }

    }

    private function checkBotOrderPlacingInterval(Carbon $start, int $intervalInSec): bool
    {
        $end = Carbon::now();
        $differenceInSeconds = $end->diffInSeconds($start);

        return $differenceInSeconds >= $intervalInSec;
    }
}
