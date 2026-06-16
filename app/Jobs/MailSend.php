<?php

namespace App\Jobs;

use App\Http\Services\MailService;
use App\Http\Services\MyCommonService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class MailSend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->data = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $mailService = app(MailService::class);
            $data = $this->data;
            $to = $data['to'];
            $name = $data['name'];
            $subject = $data['subject'];
            // $getUser = User::where('email',$data['to'])->first();
            // if ($getUser) {
            //     $commonService =  new MyCommonService();
            //     $commonService->sendNotificationToUserUsingSocket($getUser->id,$subject,$subject);
            // }
            $mailService->send($data['mailTemplate'], $data, $to, $name, $subject);

        } catch (\Exception $e) {
            storeException("send otp email job :", $e->getMessage());
        }
    }
}
