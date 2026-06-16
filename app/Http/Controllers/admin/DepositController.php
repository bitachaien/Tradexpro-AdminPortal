<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Repositories\CustomTokenRepository;
use App\Http\Services\BitgoWalletService;
use App\Http\Services\DepositService;
use App\Http\Services\WalletService;
use App\Jobs\PendingDepositAcceptJob;
use App\Jobs\PendingDepositRejectJob;
use App\Model\AdminReceiveTokenTransactionHistory;
use App\Model\DepositeTransaction;
use App\Model\EstimateGasFeesTransactionHistory;
use App\Model\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\DepositRequest;
use App\Jobs\TokenReceiveToAdminJob;
use App\Model\AffiliationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

class DepositController extends Controller
{
    // gas send history
    public function adminGasSendHistory(Request $request)
    {
        $data['title'] = __('Admin Estimate Gas Sent History');
        if ($request->ajax()) {
            $items = EstimateGasFeesTransactionHistory::join('deposite_transactions', 'deposite_transactions.id', '=','estimate_gas_fees_transaction_histories.deposit_id')
                ->select('estimate_gas_fees_transaction_histories.*','deposite_transactions.coin_type as token');

            return datatables()->of($items)
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('status', function ($item) {
                    return deposit_status($item->status);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('admin.transaction.deposit.gas_sent_history', $data);
    }

    // token receive history
    public function adminTokenReceiveHistory(Request $request)
    {
        $data['title'] = __('Admin Token Receive History');
        if ($request->ajax()) {
            $items = AdminReceiveTokenTransactionHistory::join('deposite_transactions', 'deposite_transactions.id', '=','admin_receive_token_transaction_histories.deposit_id')
                ->select('admin_receive_token_transaction_histories.*','deposite_transactions.coin_type');

            return datatables()->of($items)
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('status', function ($item) {
                    return deposit_status($item->status);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('admin.transaction.deposit.token_receive_history', $data);
    }

    // token pending deposit history
    public function adminPendingDepositHistory(Request $request)
    {
        $data['buy_token'] = false;
        if (Schema::hasTable('token_buy_histories')) {
            $data['buy_token'] = true;
        }
        $data['title'] = __('Pending Token Deposit History');

        if ($request->ajax()) {
            $items = DepositeTransaction::join('coins','coins.coin_type', '=', 'deposite_transactions.coin_type')
                ->where(['deposite_transactions.address_type' => ADDRESS_TYPE_EXTERNAL])
                ->where('deposite_transactions.is_admin_receive', STATUS_PENDING)
                ->select('deposite_transactions.*')
                ->whereIn('coins.network',[ERC20_TOKEN,BEP20_TOKEN,TRC20_TOKEN,MATIC_TOKEN])
                ->orderBy('id','desc');

            return datatables()->of($items)
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('status', function ($item) {
                    return deposit_status($item->status);
                })
                ->addColumn('actions', function ($wdrl) {
                    $action = '<ul>';
                    $action .= accept_html('adminPendingDepositAccept',encrypt($wdrl->id));
                    $action .= '<ul>';

                    return $action;
                })
                ->rawColumns(['actions','status'])
                ->make(true);
        }

        return view('admin.transaction.deposit.token_pending_deposit_history', $data);
    }

    // pending deposit reject process
    public function adminPendingDepositReject($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transaction = DepositeTransaction::where(['id' => $wdrl_id, 'status' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])->first();

            if (!empty($transaction)) {
                dispatch(new PendingDepositRejectJob($transaction,Auth::id()))->onQueue('deposit');
                return redirect()->back()->with('success', __('Pending deposit reject process goes to queue. Please wait sometimes'));
            } else {
                return redirect()->back()->with('dismiss', __('Pending deposit not found'));
            }
        }
    }

    // pending deposit accept process
    public function adminPendingDepositAccept($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transactions = DepositeTransaction::join('coins','coins.coin_type', '=', 'deposite_transactions.coin_type')
                ->where(['deposite_transactions.address_type' => ADDRESS_TYPE_EXTERNAL])
                ->where('deposite_transactions.is_admin_receive', STATUS_PENDING)
                ->where('deposite_transactions.id', $wdrl_id)
                ->select('deposite_transactions.*')
                ->whereIn('coins.network',[ERC20_TOKEN,BEP20_TOKEN,TRC20_TOKEN,MATIC_TOKEN])
                ->first();
            if (!empty($transactions)) {
                $tokenRepo = new CustomTokenRepository();
                $response = $tokenRepo->tokenReceiveManuallyByAdmin($transactions,Auth::id());

                // dispatch(new PendingDepositAcceptJob($transactions,Auth::id()))->onQueue('deposit');
                if ($response['success']) {
                    return redirect()->back()->with('success', __('Pending deposit accept process goes to queue. Please wait sometimes'));
                } else {
                    return redirect()->back()->with('dismiss',$response['message']);
                }
            } else {
                return redirect()->back()->with('dismiss', __('Pending deposit not found'));
            }
        }
    }

    public function adminCheckDeposit()
    {
        $data['title'] = __('Check Deposit');
        $data['coin_list'] = Coin::where(['status' => STATUS_ACTIVE])->get();
        return view('admin.transaction.deposit.check-deposit', $data);
    }

    public function submitCheckDeposit(DepositRequest $request)
    {
        try{
            $service = new DepositService();
            $response = $service->checkDepositByHash($request->network,$request->coin_type,$request->transaction_id,$request->type);
            if ($response['success'] == true) {
                if ($request->type == CHECK_DEPOSIT) {
                    $data = $response['data'];
                    $data['network'] = $request->network;
                    $data['coin_type'] = $request->coin_type;
                    $data['transaction_id'] = $request->transaction_id;
                    $data['type'] = $request->type;
                    $data['title'] = __('Transaction Details');
                    $data['coin_list'] = Coin::where(['status' => STATUS_ACTIVE])->get();

                    return view('admin.transaction.deposit.check-deposit', $data);
                }
            } else {
                return redirect()->route('adminCheckDeposit')->with('dismiss', $response['message']);
            }
        }catch (\Exception $e) {
            storeException("submitCheckDeposit",$e->getMessage());
            return redirect()->route('adminCheckDeposit')->with('dismiss', $e->getMessage());
        }
    }

    public function icoTokenBuyListAccept()
    {
        $tokenBuyHistories = DB::table('token_buy_histories')->where('token_buy_histories.status',STATUS_ACCEPTED)
                                        ->join('coins','token_buy_histories.coin_id', '=', 'coins.id')
                                        ->join('ico_tokens','token_buy_histories.token_id', '=', 'ico_tokens.id')
                                        ->join('wallet_address_histories','token_buy_histories.wallet_id', '=', 'wallet_address_histories.wallet_id')
                                        ->where('coins.is_listed',STATUS_ACTIVE)
                                        ->where('token_buy_histories.is_admin_receive',STATUS_PENDING)
                                        ->select('token_buy_histories.*','coins.coin_type as coin_type',
                                            'ico_tokens.wallet_address as from_address',
                                            'wallet_address_histories.address as address',
                                            'token_buy_histories.blockchain_tx as transaction_id')->get();

        return datatables()->of($tokenBuyHistories)
                ->addColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('status', function ($item) {
                    return deposit_status($item->status);
                })
                ->addColumn('actions', function ($wdrl) {
                    $action = '<ul>';
                    $action .= accept_html('adminReceiveBuyTokenAmount',encrypt($wdrl->id));
                    $action .= '<ul>';

                    return $action;
                })
                ->rawColumns(['actions','status'])
                ->make(true);
    }

    // admin token receive process
    public function adminReceiveBuyTokenAmount($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transactions = DB::table('token_buy_histories')->where('token_buy_histories.status',STATUS_ACCEPTED)
                ->where('token_buy_histories.is_admin_receive',STATUS_PENDING)
                ->join('coins','token_buy_histories.coin_id', '=', 'coins.id')
                ->join('ico_tokens','token_buy_histories.token_id', '=', 'ico_tokens.id')
                ->join('wallet_address_histories','token_buy_histories.wallet_id', '=', 'wallet_address_histories.wallet_id')
                ->where('coins.is_listed',STATUS_ACTIVE)
                ->select('token_buy_histories.*','coins.coin_type as coin_type',
                    'ico_tokens.wallet_address as from_address',
                    'wallet_address_histories.address as address',
                    'token_buy_histories.blockchain_tx as transaction_id')
                    ->first();

            if (!empty($transactions)) {
                dispatch(new TokenReceiveToAdminJob($transactions,Auth::id()))->onQueue('deposit');
                return redirect()->back()->with('success', __("Token accept to admin address, process goes to queue. Please wait sometimes, don't click multiple"));
            } else {
                return redirect()->back()->with('dismiss', __('Data not found'));
            }
        }
    }


}
