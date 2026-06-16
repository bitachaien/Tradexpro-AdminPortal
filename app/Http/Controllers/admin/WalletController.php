<?php

namespace App\Http\Controllers\admin;

use App\Model\Wallet;
use App\Exports\WalletList;
use App\Model\WalletNetwork;
use App\User;
use Illuminate\Http\Request;
use App\Model\WalletSwapHistory;
use Illuminate\Support\Facades\DB;
use App\Model\AdminSendCoinHistory;
use App\Model\WalletAddressHistory;
use App\Http\Controllers\Controller;
use App\Http\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Admin\DeductWalletRequest;
use App\Http\Requests\Admin\SendCoinBalanceRequest;
use App\Http\Requests\Admin\TransactionExportRequest;

class WalletController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service =  new WalletService();
    }
    // my wallet list
    public function myWalletList(Request $request)
    {
        $data['title'] = __('My Wallet List');
        create_coin_wallet(Auth::id());
        if($request->ajax()){
            $data['wallets'] = Wallet::join('coins','coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.user_id'=> Auth::id(), 'wallets.type'=> PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('wallets.id', 'ASC')
                ->select('wallets.*', 'coins.coin_icon');

            return datatables()->of($data['wallets'])
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->make(true);
        }

        return view('admin.profile.wallet_list',$data);
    }

    // all personal wallet list
    public function adminWalletList(Request $request)
    {
        $data['title'] = __('Wallet List');
        $data['sub_menu'] = 'wallet_list';
        if($request->ajax()){
            $data['wallets'] = Wallet::join('users','users.id','=','wallets.user_id')
                ->join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.type'=>PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('wallets.id', 'desc')
                ->select(
                    'wallets.id',
                    'wallets.name'
                    ,'wallets.coin_type'
                    ,'wallets.balance'
                    ,'wallets.created_at'
                    ,'users.first_name'
                    ,'users.last_name'
                    ,'users.email'
                );

            return datatables()->of($data['wallets'])
                ->addColumn('coin_type', function ($item) {return $item->coin_type;})
                ->addColumn('user_name',function ($item){return $item->first_name.' '.$item->last_name;})
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->addColumn('actions', function ($item) {
                    $items =  '<ul class="d-flex activity-menu">';
                    $items .='<li class="viewuser"><a title="deduct balance" href="' . route('deductWalletBalance', encrypt($item->id)) . '"><i class="fa fa-money"></i></a> </li>';

                    $items .= '</ul>';
                    return $items;
                })
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%$keyword%"]);
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('users.email', 'LIKE', "%$keyword%");
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.wallet.index',$data);
    }

    // send coin wallet list
    public function adminWalletSendList(Request $request)
    {
        $data['title'] = __('Send Coin List');
        $data['sub_menu'] = __('send_coin_list');
        if($request->ajax()){
            $data['wallets'] = AdminSendCoinHistory::join('wallets','wallets.id','=','admin_send_coin_histories.wallet_id')
                ->join('users', 'users.id', '=', 'admin_send_coin_histories.user_id')
                ->orderBy('admin_send_coin_histories.id', 'desc')
                ->select(
                    'admin_send_coin_histories.id',
                    'wallets.name'
                    ,'wallets.coin_type'
                    ,'wallets.balance'
                    ,'admin_send_coin_histories.created_at'
                    ,'users.first_name'
                    ,'users.last_name'
                    ,'users.email',
                    'admin_send_coin_histories.amount'
                );

            return datatables()->of($data['wallets'])
                ->addColumn('user_name',function ($item){return $item->first_name.' '.$item->last_name;})
                ->addColumn('created_at', function ($item) {return $item->created_at;})
                ->addColumn('actions', function ($item) {
                    $action = '<ul>';
                    $action .= delete_html('adminSendBalanceDelete',encrypt($item->id));
                    $action .= '<ul>';

                    return $action;
                })
//                ->addColumn('updated_by', function ($item) {return $item->author->first_name;})
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.wallet.send_coin_history',$data);
    }

    // Swap coin wallet list
    public function adminSwapCoinHistory(Request $request)
    {
        $data['title'] = __('Swap Coin History');
        $data['sub_menu'] = __('swap_coin_history');
        if($request->ajax()){
            $data['history'] = WalletSwapHistory::where("wallet_swap_histories.status",STATUS_ACTIVE)->with("user");
            $response = datatables($data['history'])
                ->addColumn('email',   fn ($item) => $item->user->eamil)
                ->addColumn('request', fn ($item) => $item->requested_amount." ".$item->from_coin_type)
                ->addColumn('convert', fn ($item) => $item->converted_amount." ".$item->to_coin_type)
                ->addColumn('rate',    fn ($item) => $item->rate)
                ->make(true);
            return $response;
        }
        return view('admin.wallet.swap_coin_history',$data);
    }

    // send user wallet balance
    public function adminSendWallet(Request $request)
    {
        $data['title'] = __('Send Wallet Balance');
        $data['sub_menu'] = __('send_wallet');
        $data['is_user_selected'] = false;

        if(isset($request->unique_code))
        {
            $data['is_user_selected'] = true;
            $data['selected_user'] = User::find(decrypt($request->unique_code));

            $data['wallets'] = Wallet::join('users','users.id', '=', 'wallets.user_id')
                       ->where('user_id', decrypt($request->unique_code))
                        ->select('users.first_name','users.last_name','users.email','wallets.*')
                        ->get();
        }


        return view('admin.wallet.send_wallet_coin', $data);
    }

    // admin send coin process
    public function adminSendBalanceProcess(SendCoinBalanceRequest $request)
    {
        $response = $this->service->sendCoinBalanceToUser($request);
        if($response['success'] == true) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('dismiss', $response['message']);
        }
    }

    public function adminSendBalanceDelete($id)
    {
        $response = $this->service->adminSendBalanceDelete(decrypt($id));
        if($response['success']== true)
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }

    }

     // all wallet address list
    public function walletAddressList(Request $request)
    {
        $data['title'] = __('Wallet Address List');
        $data['sub_menu'] = 'wallet_address_list';
        $data['address_list'] = WalletAddressHistory::with(["wallet:id,user_id", "wallet.user:id,email"])->orderBy('id','desc')->get();
        $data['address_network_list'] = WalletNetwork::orderBy('id','desc')->where('address','<>','')->get();
        return view('admin.wallet.address_list',$data);
    }

    public function deductWalletBalance($wallet_id)
    {
        $data['title'] = __('Deduct User wallet balance');
        $data['sub_menu'] = 'wallet_list';
        $wallet_details = Wallet::find(decrypt($wallet_id));

        if(isset($wallet_details))
        {
            $data['wallet_details'] = $wallet_details;

            return view('admin.wallet.deduct-user-wallet', $data);
        }else{
            return back()->with('success',__('Invalid Request!'));
        }

    }

    public function deductWalletBalanceSave(DeductWalletRequest $request)
    {
        $response = $this->service->deductWalletBalanceSave($request);
        if($response['success'])
        {
            return redirect()->route('adminWalletList')->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function adminWalletListExport(TransactionExportRequest $request)
    {
        try{
            return Excel::download(new WalletList($request), 'User_Wallet'.($request->export_to ?? '.csv'));
        }catch(\Exception $e){
            storeException('adminWalletListExport', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminActiveUserList(Request $request)
    {
        if($request->ajax()){
            $userList = User::where('status', STATUS_ACTIVE);
            $response = datatables()->of($userList)
                ->addColumn('name',function ($item)
                    {return $item->first_name;})
                ->addColumn('actions', function ($item) {
                    $action = '<ul>';
                    $action .= '<a class="button-primary theme-btn btn-sm"
                                    href='.route('adminSendWallet',['unique_code'=>encrypt($item->id)]).'>'.__('Select').'</a>';
                    $action .= '<ul>';

                    return $action;
                })
                ->rawColumns(['actions'])
                ->make(true);
            return $response;
        }
    }

}
