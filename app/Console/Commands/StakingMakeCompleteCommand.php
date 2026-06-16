<?php

namespace App\Console\Commands;

use App\Http\Services\StakingOfferService;
use App\Traits\ResponseHandlerTrait;
use Illuminate\Console\Command;

class StakingMakeCompleteCommand extends Command
{
    use ResponseHandlerTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:make-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Staking investment make complete';

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
        return $this->handlerGeneralResponse(function () {
            return (new StakingOfferService)->makeCompleteInvestment();
        });
    }
}
