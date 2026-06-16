<?php

namespace App\Console\Commands;

use App\Http\Repositories\CustomTokenRepository;
use Illuminate\Console\Command;

class CustomTokenDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-token-deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ERC20 and TRC20 token. Monitor the blockchain deposit and if find new deposit then send this token to admin address';

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
     * @return mixed
     */
    public function handle()
    {
        storeBotException('CustomTokenDeposit called', date('Y-m-d H:i:s'));
        $repo = new CustomTokenRepository();
        $repo->depositCustomToken();
    }
}
