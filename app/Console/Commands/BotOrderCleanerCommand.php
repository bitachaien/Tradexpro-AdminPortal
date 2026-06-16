<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Services\TradingBotServices\ProcessedBotOrderRemoverService;
use Illuminate\Console\Command;

class BotOrderCleanerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot-order:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes bot to bot transactions and bot orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private BotCoinPairRepositoryInterface $botCoinPairRepository,
        private ProcessedBotOrderRemoverService $botOrderCleanerService
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $coinPairs = $this->botCoinPairRepository->getBotStatusActivePairs();
        $superAdminId = get_super_admin_id();

        foreach ($coinPairs as $coinPair) {
            $this->botOrderCleanerService->cleanBotOrders(
                $coinPair->base_coin_id, 
                $coinPair->trade_coin_id,
                $superAdminId,
                config('bot.minimum_transaction_rows') 
            );
        }
    }
}
