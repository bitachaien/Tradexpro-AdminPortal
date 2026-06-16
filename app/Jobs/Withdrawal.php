<?php

namespace App\Jobs;

use App\Http\Services\TransactionService;
use App\Http\Services\TransService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class Withdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $request ;
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $request = (array)$this->request;
            $trans = new TransService();
            $response = $trans->send($request);
            storeException('Withdrawal queue res',json_encode($response));

        }
        catch(\Exception $e) {
            storeException('Withdrawal queue ex',$e->getMessage());
            return false;
        }
    }
}
