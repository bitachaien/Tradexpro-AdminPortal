<?php

namespace App\Observers;

use App\User;
use App\Model\Wallet;
use App\Jobs\MailSend;
use App\Http\Services\MyCommonService;
use App\Model\DepositeTransaction;

class DepositeTransactionObserver
{
    /**
     * Handle the DepositeTransaction "created" event.
     *
     * @param  \App\DepositeTransaction  $depositeTransaction
     * @return void
     */
    public function created(DepositeTransaction $depositeTransaction): void
    {
        try {
            $recivererWallet = Wallet::whereId($depositeTransaction->receiver_wallet_id)->first();
            $receiver = User::findOrFail($recivererWallet->user_id ?? 0);
            if ($depositeTransaction->status == STATUS_PENDING) {
                $title = __("New pending deposit");
                $body = __("You received $depositeTransaction->amount $depositeTransaction->coin_type deposit request. \n
                From address : $depositeTransaction->from_address \n
                but it's in under approval");
                $this->sendEmailAndNotification($title, $body, $receiver);
            }
            if($depositeTransaction->status == STATUS_ACCEPTED) {
                if (!empty($depositeTransaction->updated_by)) {
                    $title = __("Pending deposit approved by system");
                    $body = __("You received $depositeTransaction->amount $depositeTransaction->coin_type deposit. \n
                    From address : $depositeTransaction->from_address \n
                    Deposit transaction id : $depositeTransaction->transaction_id.");
                    $this->sendEmailAndNotification($title, $body, $receiver);
                } else {
                    $title = __("New deposit received");
                    $body = __("You received $depositeTransaction->amount $depositeTransaction->coin_type deposit successfully. \n
                    From address : $depositeTransaction->from_address \n
                    Deposit transaction id : $depositeTransaction->transaction_id.");
                    $this->sendEmailAndNotification($title, $body, $receiver);
                }

            }

        } catch (\Exception $e) {
            storeException('DepositeTransactionObserver create err', $e->getMessage());
        }
    }

    /**
     * Handle the DepositeTransaction "updated" event.
     *
     * @param  \App\DepositeTransaction  $depositeTransaction
     * @return void
     */
    public function updated(DepositeTransaction $depositeTransaction)
    {
        try {
            $user = $depositeTransaction->receiverWallet->user;
            if ($user) {
                if($depositeTransaction->isDirty('status')){
                    $status = $depositeTransaction->status;
                    $old_status = $depositeTransaction->getOriginal('status');
                    if($depositeTransaction->status == STATUS_REJECTED) {
                        $title = __("Pending deposit rejected");
                        $body = __("You pending $depositeTransaction->amount $depositeTransaction->coin_type deposit is rejected by system. \n
                        From address : $depositeTransaction->from_address \n");
                        $this->sendEmailAndNotification($title, $body, $user);
                    }
                    if($depositeTransaction->status == STATUS_ACCEPTED) {
                        if (!empty($depositeTransaction->updated_by)) {
                            $title = __("Pending deposit approved by system");
                            $body = __("You received $depositeTransaction->amount $depositeTransaction->coin_type deposit that was approved by system. \n
                            From address : $depositeTransaction->from_address \n
                            Deposit transaction id : $depositeTransaction->transaction_id.");
                            $this->sendEmailAndNotification($title, $body, $user);
                        } else {
                            $title = __("New deposit received");
                            $body = __("You received $depositeTransaction->amount $depositeTransaction->coin_type deposit successfully. \n
                            From address : $depositeTransaction->from_address \n
                            Deposit transaction id : $depositeTransaction->transaction_id.");
                            $this->sendEmailAndNotification($title, $body, $user);
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            storeException('DepositeTransactionObserver update err', $e->getMessage());
        }
    }

    /**
     * Handle the DepositeTransaction "deleted" event.
     *
     * @param  \App\DepositeTransaction  $depositeTransaction
     * @return void
     */
    public function deleted(DepositeTransaction $depositeTransaction)
    {
        //
    }

    /**
     * Handle the DepositeTransaction "restored" event.
     *
     * @param  \App\DepositeTransaction  $depositeTransaction
     * @return void
     */
    public function restored(DepositeTransaction $depositeTransaction)
    {
        //
    }

    /**
     * Handle the DepositeTransaction "force deleted" event.
     *
     * @param  \App\DepositeTransaction  $depositeTransaction
     * @return void
     */
    public function forceDeleted(DepositeTransaction $depositeTransaction)
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
