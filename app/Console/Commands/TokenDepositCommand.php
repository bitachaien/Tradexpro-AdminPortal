<?php

namespace App\Console\Commands;

use App\Http\Repositories\CustomTokenRepository;
use Illuminate\Console\Command;

class TokenDepositCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:erc20token-deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TRC20 and Polygon token deposit command';

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
        storeBotException('TokenDepositCommand called', date('Y-m-d H:i:s'));
        $repo = new CustomTokenRepository();
        $repo->depositCustomERC20Token();
    }
}
