<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuyGiftCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
   
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $finder,
        private $data,
        private $request
    )
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
