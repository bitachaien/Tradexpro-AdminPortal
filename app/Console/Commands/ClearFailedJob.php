<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearFailedJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-failed-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command for clear a day old job failed';

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
        $a = DB::table("failed_jobs")
        ->whereDate('failed_at', '<=', Carbon::yesterday()->toDateTimeString())
        ->delete();
        return 0;
    }
}
