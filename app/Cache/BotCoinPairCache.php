<?php

namespace App\Cache;

use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Http\Services\CacheService;
use Illuminate\Support\Collection;

class BotCoinPairCache
{
    public function __construct(
        private BotCoinPairRepositoryInterface $botCoinPairRepository,
        private CacheService $cacheService
    ) {}

    public function getBotStatusActivePairs(): Collection
    {
        $key = 'bot_status_active_pairs';
        $result = $this->cacheService->get($key);

        if ($result) {
            return $result;
        }

        $result = $this->botCoinPairRepository->getBotStatusActivePairs();
        $this->cacheService->setWithTimeOut($key, $result, '30');

        return $result;
    }

    public function forgetStatusActivePairs()
    {
        $this->cacheService->forget('bot_status_active_pairs');
    }
}
