<?php

namespace App\Http\Controllers\admin;

use App\User;
use App\Model\Buy;
use Carbon\Carbon;
use App\Model\Coin;
use App\Model\Sell;
use App\Model\OneDay;
use App\Model\Wallet;
use App\Jobs\SendMail;
use App\Model\Transaction;
use Illuminate\Http\Request;
use App\Model\BuyCoinHistory;
use App\Model\FiatWithdrawal;
use App\Model\MembershipClub;
use App\Model\SendMailRecord;
use App\Model\CurrencyDeposit;
use App\Model\WithdrawHistory;
use App\Model\StakingInvestment;
use App\Model\WalletSwapHistory;
use PragmaRX\Google2FA\Google2FA;
use App\Http\Services\AuthService;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Services\ERC20TokenApi;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\User2FAService;
use App\Http\Services\MyCommonService;
use App\Http\Requests\UserProfileUpdate;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\resetPasswordRequest;
use App\Model\MembershipBonusDistributionHistory;

class DashboardController extends Controller
{
    // get api data and save to db
    public function getJsonData()
    {
        $datas = json_decode(file_get_contents(storage_path() . "/chart_data.json"), true);
        $input = [];
        if (isset($datas[0])) {
            OneDay::where('id','<>',1)->delete();
            foreach ($datas as $data) {
                $input[] = [
                    'base_coin_id' => 2,
                    'trade_coin_id' => 1,
                    'interval'=> $data['time'],
                    'open'=> $data['open'],
                    'close'=> $data['close'],
                    'high'=> $data['high'],
                    'low'=> $data['low'],
                    'volume'=> $data['volumefrom'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            DB::table('tv_chart_1days')->insert($input);
        }
    }
    // admin dashboard
    public function adminDashboard()
    {
        $data['title'] = __('Admin Dashboard');
        $total_coins = Wallet::join('coins','coins.id','=','wallets.coin_id')
            ->selectRaw('sum(balance * coin_price) as totalUsd')
            ->first()->totalUsd;
        $data['total_coin'] = convert_currency($total_coins,'BTC','USDT');
        $data['total_transaction'] = Transaction::sum('amount');
        $buy_fees = Transaction::sum('buy_fees');
        $sell_fees = Transaction::sum('sell_fees');
        $w_fees = WithdrawHistory::sum('fees');
        $data['total_earning'] = $buy_fees + $sell_fees + $w_fees;
        $data['total_user'] = User::where('role', USER_ROLE_USER)->count();
        $data['active_buy'] = Buy::where(['status' => STATUS_PENDING])->count();
        $data['active_sell'] = Sell::where(['status' => STATUS_PENDING])->count();
        $data['fiat_pending_deposit'] = CurrencyDeposit::where('status',STATUS_PENDING)->count();
        $data['fiat_deposit'] = CurrencyDeposit::where('status',STATUS_ACCEPTED)->count();
        $data['fiat_withdrawal'] = FiatWithdrawal::where('status',STATUS_ACCEPTED)->count();
        $data['swap'] = WalletSwapHistory::query()
                        ->where('status',STATUS_ACCEPTED)
                        ->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ])
                        ->count();
        $data['staking'] = StakingInvestment::where('status','<>', STAKING_INVESTMENT_STATUS_CANCELED)->sum('investment_amount');
        $data['crypto_deposit'] = DepositeTransaction::where(['status' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])->count();

        $allMonths = all_months();
        // deposit
        $monthlyDeposits = DepositeTransaction::select(DB::raw('sum(amount) as totalDepo'), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', STATUS_SUCCESS)
            ->groupBy('months')
            ->get();

        if (isset($monthlyDeposits[0])) {
            foreach ($monthlyDeposits as $depsit) {
                $data['deposit'][$depsit->months] = $depsit->totalDepo;
            }
        }
        $allDeposits = [];
        foreach ($allMonths as $month) {
            $allDeposits[] =  isset($data['deposit'][$month]) ? $data['deposit'][$month] : 0;
        }
        $data['monthly_deposit'] = $allDeposits;

        // withdrawal
        $monthlyWithdrawals = WithdrawHistory::select(DB::raw('sum(amount) as totalWithdraw'), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', STATUS_SUCCESS)
            ->groupBy('months')
            ->get();

        if (isset($monthlyWithdrawals[0])) {
            foreach ($monthlyWithdrawals as $withdraw) {
                $data['withdrawal'][$withdraw->months] = $withdraw->totalWithdraw;
            }
        }
        $allWithdrawal = [];
        foreach ($allMonths as $month) {
            $allWithdrawal[] =  isset($data['withdrawal'][$month]) ? $data['withdrawal'][$month] : 0;
        }
        $data['monthly_withdrawal'] = $allWithdrawal;

        $data['pending_withdrawals'] = WithdrawHistory::where(['status' => STATUS_PENDING])
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return view('admin.dashboard', $data);
    }

    // admin profile
    public function adminProfile(Request $request)
    {
        $data['title'] = __('Profile');
        $data['tab']='profile';
        $data['user']= User::where('id', Auth::id())->first();
        $default = allsetting();
        $data['settings'] = $default;
        $data['countries'] = country();
        $two_factor_list = $default['two_factor_list'] ?? '{}';
        $data['two_factor'] = json_decode($two_factor_list,true);
        $google2fa = new Google2FA();
        $google2fa->setAllowInsecureCallToGoogleApis(true);
        $data['google2fa_secret'] = $google2fa->generateSecretKey();
        $user = Auth::user();
        // $google2fa_url = $google2fa->getQRCodeGoogleUrl(
        //     isset($default['app_title']) && !empty($default['app_title']) ? $default['app_title'] : 'Tredexpro',
        //     isset(Auth::user()->email) && !empty(Auth::user()->email) ? Auth::user()->email : 'admin@email.com',
        //     $data['google2fa_secret']
        // );
        // $data['qrcode'] = $google2fa_url;

        $companyName = !empty(allsetting('app_title')) ? allsetting('app_title') : 'Your Company Name';
        $companyEmail = isset($user->email) && !empty($user->email) ? $user->email : 'user@email.com';

        $google2fa_url = $google2fa->getQRCodeUrl($companyName, $companyEmail, $data['google2fa_secret']);

        $data['qrcode'] = $google2fa_url;

        return view('admin.profile.index',$data);
    }

    // enable / disable google auth secret code
    public function g2fa_enable(Request $request){
        try{
            $two_factor = new User2FAService();
            $response = $two_factor->g2fSecretSave($request);
            if($response['success'])
                return redirect()->back()->with("success",$response["message"]);
            return redirect()->back()->with("dismiss",$response["message"]);
        }catch (\Exception $e){
            storeException("save google 2fa :",$e->getMessage());
            return redirect()->back()->with("dismiss",__("Something went wrong"));
        }
    }

    public function updateTwoFactor(Request $request)
    {
        try{
            $two_factor = new User2FAService();
            $response = $two_factor->updateTwoFactor($request);
            if($response['success'])
                return redirect()->back()->with("success",$response["message"]);
            return redirect()->back()->with("dismiss",$response["message"]);
        }catch (\Exception $e){
            storeException("updateTwoFactor: ",$e->getMessage());
            return redirect()->back()->with("dismiss",__("Something went wrong"));
        }
    }

    // update user profile
    public function UserProfileUpdate(UserProfileUpdate $request)
    {

        if (strpos($request->phone, '+') !== false) {
            return redirect()->back()->with('dismiss',__("Don't put plus sign with phone number"));
        }
        if(!country($request->country))
        {
            return redirect()->back()->with('dismiss',__("Invalid country code!"));
        }
        if(!empty($request->email)){
            $data['email'] = $request->email;
        }
        $data['nickname'] = $request->nickname;
        $data['first_name'] = $request->first_name;
        $data['last_name'] = $request->last_name;
        $data['country'] = $request->country;
        $data['api_access_allow_user'] = $request->api_access_allow_user;
        $user = (!empty($request->id)) ? User::find(decrypt($request->id)) : Auth::user();

        if ($user->phone != $request->phone){
            $data['phone'] =  $request->phone;
            $data['phone_verified'] = null;
        }
        $user->update($data);

        return redirect()->back()->with('success',__('Profile updated successfully'));
    }

    // profile upload image
    public function uploadProfileImage(Request $request)
    {
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
        $rules['file_one'] = "required|image|mimes:jpg,jpeg,png,jpg,gif,svg|max:$file_size|dimensions:max_width=500,max_height=500";
        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            $message = $validator->getMessageBag()->getMessages()['file_one'][0];
            if ($message == 'The file one has invalid image dimensions.')
                $message =  __('Image size must be less than (height:500,width:500)');

            return redirect()->back()->with('dismiss',$message);
        }
        try {
            $img = $request->file('file_one');
            $user_data = (!empty($request->id) ) ? User::find(decrypt($request->id)) : Auth::user();

            if ($img !== null) {
                $photo = uploadFile($img, IMG_USER_PATH, !empty($user_data->photo) ? $user_data->photo : '');
                $user = User::find($user_data->id);
                $user->photo  = $photo;
                $user->save();
                return redirect()->back()->with('success',__('Profile picture uploaded successfully'));
            } else {
                return redirect()->back()->with('dismiss',__('Please input a image'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('dismiss', $e->getMessage());
        }

    }

    public function changePasswordSave(resetPasswordRequest $request)
    {
        $service = new AuthService();
        $change = $service->changePassword($request);
        if ($change['success']) {
            return redirect()->back()->with('success',$change['message']);
        } else {
            return redirect()->back()->with('dismiss',$change['message']);
        }
    }

    // send email
    public function sendEmail()
    {
        $data['title'] = __('Send Email');

        return view('admin.notification.email', $data);
    }

    //send notification
    public function sendNotification()
    {
        $data['title'] = __('Send Notification');

        return view('admin.notification.notification', $data);
    }

    // send mail process
    public function sendEmailProcess(Request $request)
    {
        $rules = [
            'subject' => 'required',
            'email_message' => 'required',
            'email_type' => 'required'
        ];
        $messages = [
            'subject.required' => __('Subject field can not be empty'),
            'email_message.required' => __('Message field can not be empty'),
            'email_type.required' => __('Email type field can not be empty'),
        ];
        $validator = Validator::make( $request->all(), $rules, $messages );
        if ($validator->fails()) {
            return redirect()->back()->withInput()->with(['dismiss' => $validator->errors()->first() ]);
        } else {
            $data['subject'] = $request->subject;
            $data['email_message'] = $request->email_message;
            $data['type'] = $request->email_type;
            $data['mailTemplate'] = emailTemplateName('genericemail');

            if (!empty($request->email_headers)) {
                $data['email_header'] = $request->email_headers;
            }
            if (!empty($request->footers)) {
                $data['email_footer'] = $request->footers;
            }

//            app(OldCommonService::class)->sendEmailToAlUser($data);
            dispatch(new SendMail($data))->onQueue('send-mail');

            return redirect()->back()->with('success',__('Mail sent successfully'));
        }
    }

    // send notification process
    public function sendNotificationProcess(Request $request)
    {
        $rules = [
            'title' => 'required',
            'notification_body' => 'required',
        ];

        $messages = [
            'title.required' => 'Notification title can not be empty',
            'notification_body.required' => 'Notification body can not be empty',
        ];

        $this->validate($request, $rules, $messages);

        $service = new MyCommonService();
        try {
            $response = $service->sendNotificationProcess($request);
            return redirect()->back()->with(['success' => 'Notification sent successfully']);

        } catch (\Exception $exception) {
            return redirect()->back()->with(['dismiss' => 'Something went wrong. Please try again']);
        }
    }

    /*
    * clearEmailRecord
    *
    * clear email record
    *
    */

    public function clearEmailRecord()
    {
        $record = SendMailRecord::all();
        if(count($record) > 0) {
            SendMailRecord::truncate();
            return redirect()->back()->with('success',__('All records are deleted successfully'));
        } else {
            return redirect()->back()->with('dismiss',__('Records are already deleted'));
        }
    }

    // admin total Earning Report
    public function adminEarningReport(Request $request)
    {
        $data['title'] = __('Earning Report');
        if($request->ajax()) {
            $items = [];
            $coins =  Coin::where(['status' => STATUS_ACTIVE])->get();
            if ($request->type == 'withdraw') {
                $items = withdrawalEarnings($coins);
            } elseif($request->type == 'trade') {
                $items = withdrawalEarnings($coins);
            }
            return datatables()->of($items)
                ->addColumn('fees', function ($item) {
                    return $item['fees'].' '.$item['coin_type'];
                })
                ->make(true);
        }
        return view('admin.transaction.earning_report', $data);
    }

    // check
    public function adminDashboardCheck(Request $request)
    {
        try {
            $coin = Coin::join('coin_settings','coin_settings.coin_id','=','coins.id')
                ->where(['coins.coin_type' => $request->coin])
                ->first();
            $requestData = [];
            $api = new ERC20TokenApi($coin);
            return $api->checkingData();
        } catch(\Exception $e) {
            return redirect()->back()->with('dismiss',$e->getMessage());
        }
    }
}
