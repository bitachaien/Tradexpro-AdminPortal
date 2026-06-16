<?php

namespace App\Observers;

use App\User;
use App\Jobs\MailSend;
use App\Model\WithdrawHistory;
use App\Http\Services\MyCommonService;

class WithdrawHistoryObserver
{
    /**
     * Handle the WithdrawHistory "created" event.
     *
     * @param  \App\WithdrawHistory  $withdrawHistory
     * @return void
     */
    public function created(WithdrawHistory $withdrawHistory): void
    {
        try{
            $user = User::findOrFail($withdrawHistory->user_id);
            if ($withdrawHistory->status == STATUS_ACCEPTED) {
                $title = __("Withdraw successfully processed");
                $body = __("Your withdraw is successfully processed. \nWithdrawal transaction hash is $withdrawHistory->transaction_hash.");
                $this->sendEmailAndNotification($title, $body, $user);
            }
            if ($withdrawHistory->status == STATUS_PENDING) {
                $title = __("Withdraw request placed");
                $body = __("Your withdraw is successfully placed.\nThis withdraw is under review. So, wait for system approval.");
                $this->sendEmailAndNotification($title, $body, $user);
            }

        } catch(\Exception $e) {
            storeException('WithdrawHistoryObserver created err',$e->getMessage());
        }
    }

    /**
     * Handle the WithdrawHistory "updated" event.
     *
     * @param  \App\WithdrawHistory  $withdrawHistory
     * @return void
     */
    public function updated(WithdrawHistory $withdrawHistory)
    {
        try {
            $user = User::findOrFail($withdrawHistory->user_id);
            // if ($withdrawHistory->automatic_withdrawal == 'failed') {
            //         $title = __("Withdraw process failed");
            //         $body =  __("Your requested ").' '.$withdrawHistory->amount.$withdrawHistory->coin_type.__(' withdrawal is failed due to network or unknown issue. So we transfer it to system approval');
            //         $this->sendEmailAndNotification($title, $body, $user);
            // }
            if($withdrawHistory->isDirty('status')){
                $status = $withdrawHistory->status;
                $old_status = $withdrawHistory->getOriginal('status');
                if($old_status == STATUS_PENDING && $status == STATUS_PENDING) {
                    $title = __("Withdraw process failed");
                    $body =  __("Your requested ").' '.$withdrawHistory->amount.$withdrawHistory->coin_type.__(' withdrawal is failed due to network or unknown issue. So we transfer it to system approval');
                    $this->sendEmailAndNotification($title, $body, $user);
                }
                if(!empty($withdrawHistory->updated_by) && $status == STATUS_ACCEPTED){
                    $title = __("Withdraw request approved by system");
                    $body =  __("Your withdrawal request is approved by System. \nWithdrawal transaction hash is $withdrawHistory->transaction_hash.");
                    $this->sendEmailAndNotification($title, $body, $user);
                }
                if(empty($withdrawHistory->updated_by) && $status == STATUS_ACCEPTED){
                    $title = __("Withdraw request processed");
                    $body =  __("Your withdrawal processed successfully. \nWithdrawal transaction hash is $withdrawHistory->transaction_hash.");
                    $this->sendEmailAndNotification($title, $body, $user);
                }
                if($status == STATUS_REJECTED){
                    $title = __("System has rejected your withdrawal request");
                    $body =  __("System has rejected your withdrawal request ");
                    $this->sendEmailAndNotification($title, $body, $user);
                }
            }
        } catch(\Exception $e) {
            storeException('WithdrawHistoryObserver updated err',$e->getMessage());
        }

    }

    /**
     * Handle the WithdrawHistory "deleted" event.
     *
     * @param  \App\WithdrawHistory  $withdrawHistory
     * @return void
     */
    public function deleted(WithdrawHistory $withdrawHistory)
    {
        //
    }

    /**
     * Handle the WithdrawHistory "restored" event.
     *
     * @param  \App\WithdrawHistory  $withdrawHistory
     * @return void
     */
    public function restored(WithdrawHistory $withdrawHistory)
    {
        //
    }

    /**
     * Handle the WithdrawHistory "force deleted" event.
     *
     * @param  \App\WithdrawHistory  $withdrawHistory
     * @return void
     */
    public function forceDeleted(WithdrawHistory $withdrawHistory)
    {
        //
    }

    private function sendEmailAndNotification($title, $message, $user)
    {
        (new MyCommonService())->sendNotificationToUserUsingSocket(
            $user->id,
            $title,
            $message
        );
        $emailData = [
            'to' => $user->email,
            'name' => $user->first_name.' '.$user->last_name,
            'subject' => $title,
            'email_header' => $title,
            'email_message' => $message,
            'mailTemplate' => emailTemplateName('genericemail')
        ];
        dispatch(new MailSend($emailData))->onQueue('send-mail');
    }
}
