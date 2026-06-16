<?php

namespace App\Console\Commands;

use App\Http\Services\StakingOfferService;
use Illuminate\Console\Command;

class StakingInvestmentReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:give-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Staking Investment returned';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
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
        $stakingOfferService = new StakingOfferService;
        $stakingOfferService->givePayment();

        storeBotException('Give payment', 'Staing Investment payment return');
    }
}
