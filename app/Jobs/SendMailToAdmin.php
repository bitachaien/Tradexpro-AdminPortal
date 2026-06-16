<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Services\RoleManagmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMailToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $password;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event,$password)
    {
        $this->data = $event;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            app(RoleManagmentService::class)->sendEmailToAdmin($this->data,$this->password);
        } catch (\Exception $e) {
            storeException('SendMailToAdmin', $e->getMessage());
        }
    }
}
