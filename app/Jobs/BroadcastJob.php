<?php

namespace App\Jobs;

use App\Http\Services\BroadcastService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chanel;
    protected $data;
    protected $event;

    /**
     * Create a new job instance.
     *
     * @param $transferBalanceData
     * @param $userWallet
     */
    public function __construct($chanel, $event, $data)
    {
        $this->chanel = $chanel;
        $this->event = $event;
        $this->data = $data;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $broadcastService = new BroadcastService();
        return $broadcastService->broadCast($this->chanel, $this->event, $this->data);
    }
}