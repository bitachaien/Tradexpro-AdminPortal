<?php
namespace App\Http\Services;

use App\Model\DepositeTransaction;
use App\Model\Notification;
use App\Model\SendMailRecord;
use App\Model\Wallet;
use App\Model\WithdrawHistory;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;

class MyCommonService
{
    // user deposit
    public function AuthUserDiposit($start_date, $end_date, $type = null)
    {
        $data = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id');
        if (!empty($type) && ($type == 'week')) {
            $data = $data->select(
                DB::raw('DAYNAME(deposite_transactions.created_at) as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );

        } elseif (!empty($type) && ($type == 'month')) {
            $data = $data->select(
                DB::raw('DATE_FORMAT(deposite_transactions.created_at, "%d-%b")  as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );

        } else {
            $data = $data->select(
                DB::raw('MONTHNAME(deposite_transactions.created_at) as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );
        }

        $data = $data->where('deposite_transactions.status', STATUS_SUCCESS)
            ->where('wallets.user_id', Auth::id())
            ->whereBetween('deposite_transactions.created_at', [$start_date, $end_date])
            ->groupBy('month')
            ->pluck('balance', 'month');

        return $data;
    }

    // user withdrawal history
    public function AuthUserWithdraw($start_date, $end_date, $type = null)
    {
        $data = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id');
        if (!empty($type) && ($type == 'week')) {

            $data = $data->select(
                DB::raw('DAYNAME(withdraw_histories.created_at) as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );

        } elseif (!empty($type) && ($type == 'month')) {

            $data = $data->select(
                DB::raw('DATE_FORMAT(withdraw_histories.created_at, "%d-%b")  as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );


        } else {
            $data = $data->select(
                DB::raw('MONTHNAME(withdraw_histories.created_at) as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );
        }
        $data = $data->where('withdraw_histories.status', STATUS_SUCCESS)
            ->where('wallets.user_id', Auth::id())
            ->whereBetween('withdraw_histories.created_at', [$start_date, $end_date])
            ->groupBy('month')
            ->pluck('balance', 'month');

        return $data;
    }

    // all deposit and withdrawal data for chart
    public function userDiposit($start_date, $end_date, $type = null)
    {
        $data = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id');
        if (!empty($type) && ($type == 'week')) {

            $data = $data->select(
                DB::raw('DAYNAME(deposite_transactions.created_at) as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );

        } elseif (!empty($type) && ($type == 'month')) {
            $data = $data->select(
                DB::raw('DATE_FORMAT(deposite_transactions.created_at, "%d-%b")  as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );

        } else {
            $data = $data->select(
                DB::raw('MONTHNAME(deposite_transactions.created_at) as month'),
                DB::raw('SUM(deposite_transactions.amount) as balance')
            );
        }

        $data = $data->where('deposite_transactions.status', STATUS_SUCCESS)
//            ->where('wallets.user_id',Auth::id())
            ->whereBetween('deposite_transactions.created_at', [$start_date, $end_date])
            ->groupBy('month')
            ->pluck('balance', 'month');

        return $data;
    }

    // user withdraw
    public function userWithdraw($start_date, $end_date, $type = null)
    {
        $data = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id');
        if (!empty($type) && ($type == 'week')) {

            $data = $data->select(
                DB::raw('DAYNAME(withdraw_histories.created_at) as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );

        } elseif (!empty($type) && ($type == 'month')) {

            $data = $data->select(
                DB::raw('DATE_FORMAT(withdraw_histories.created_at, "%d-%b")  as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );


        } else {
            $data = $data->select(
                DB::raw('MONTHNAME(withdraw_histories.created_at) as month'),
                DB::raw('SUM(withdraw_histories.amount) as balance')
            );
        }
        $data = $data->where('withdraw_histories.status', STATUS_SUCCESS)
//            ->where('wallets.user_id',Auth::id())
            ->whereBetween('withdraw_histories.created_at', [$start_date, $end_date])
            ->groupBy('month')
            ->pluck('balance', 'month');

        return $data;
    }

    // check id
    public function checkValidId($id){
        try {
            $id = decrypt($id);
        } catch (\Exception $e) {
            return ['success'=>false];
        }
        return $id;
    }


    // notification send process
    public function sendNotificationProcess($request)
    {
        try {
            $users = User::where(['status'=>STATUS_ACTIVE, 'role'=> USER_ROLE_USER])->get();
            if (isset($users[0])) {
                foreach ($users as $user) {
                    $notification_details = Notification::create(['user_id'=>$user->id, 'title'=>$request->title, 'notification_body'=>$request->notification_body]);
                    $data['success'] = true;
                    $data['user_id'] = $user->id;
                    $data['message'] = $request->title;
                    $data['notification_details'] = $notification_details;

                    $channel = 'usernotification_'.$user->id;
                    $config = config('broadcasting.connections.pusher');
                    $pusher = new Pusher($config['key'], $config['secret'], $config['app_id'], $config['options']);

                    $test =  $pusher->trigger($channel , 'receive_notification', $data);
                }
            }
        } catch (\Exception $e) {
            storeException('sendNotificationProcess',$e->getMessage());
        }
    }

    // send email to all users
    public function sendEmailToAlUser($datas)
    {
        $mailService = app(MailService::class);

        $data['users'] = User::where(['status' => STATUS_ACTIVE, 'role'=> USER_ROLE_USER])->get();

        if(isset($data['users'][0])) {
            foreach ($data['users'] as $user) {
                $already_sent = SendMailRecord::where('user_id', $user->id)
                    ->where('email_type', $datas['type'])
                    ->first();

                if ($already_sent) {
                    continue;
                }
                $input['user_id'] = $user->id;
                $input['status'] = STATUS_ACTIVE;
                $input['email_type'] = $datas['type'];
                $email = $user->email;
                $subject = $datas['subject'];
                $name = $user->first_name.' '.$user->last_name;
                try {
                    $mailSent = $mailService->send($datas['mailTemplate'], $datas, $email, $name, $subject);
                    if ($mailSent['error']) {
                        throw new \Exception($mailSent['message'], '500');
                    }

                    SendMailRecord::create($input);
                } catch (\Exception $e) {
                    storeException('sendEmailToAlUser', $e->getMessage());
                }
            }
        }
    }


    public function sendNotificationToUserUsingSocket($user_id,$title,$message)
    {
        try {
            $notification_details = Notification::create(['user_id' => $user_id, 'title' => $title, 'notification_body' => $message]);
            $channel_name = 'notification_'.$user_id;
            $event_name = 'notification';
            $data['success'] = true;
            $data['user_id'] = $user_id;
            $data['message'] = $message;
            $data['notification_details'] = $notification_details;

            sendDataThroughWebSocket($channel_name,$event_name,$data);
        } catch (\Exception $e) {
            storeException('sendNotificationToUserUsingSocket',$e->getMessage());
        }
    }



}
