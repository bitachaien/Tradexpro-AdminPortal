<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Services\BankService;
use App\Http\Services\CurrencyDepositService;
use App\Http\Services\PaymentMethodService;
use App\Http\Services\WalletService;
use Illuminate\Http\Request;
use App\Model\CurrencyDeposit;

class CurrencyDepositController extends Controller
{
    public $service;

    function __construct()
    {
        $this->service = new CurrencyDepositService();
    }

    public function currencyDepositList()
    {
        try{
            $data['title'] = __('Deposit History');

            return view('admin.currency-deposite.pending-deposite-list',$data);

        } catch (\Exception $e) {
            storeException("currencyDepositList",$e->getMessage());
        }
    }

    public function currencyDepositPendingList(Request $request)
    {

        if ($request->ajax()) {
            $deposit_list = CurrencyDeposit::where('status',STATUS_PENDING)->orderBy('currency_deposits.id', 'desc');

            return datatables()->of($deposit_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('to_wallet', function ($query) {
                    return isset($query->wallet)?$query->wallet->name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return isset($query->bank)?$query->bank->bank_name:'N/A';
                })
                ->addColumn('created_at', function ($query) {
                    return $query->created_at;
                })
                ->addColumn('currency_amount', function ($query) {
                    return $query->currency_amount.' '.$query->currency;
                })
                ->addColumn('coin_amount', function ($query) {
                    return $query->coin_amount.' '.$query->wallet->coin_type;
                })
                ->addColumn('payment_method', function ($query) {
                    return currencyDepositPaymentMethod($query->payment->payment_method);
                })
                ->addColumn('bank_receipt', function ($query) {
                    if(!empty($query->bank_receipt)) {
                        $action = modal_image_show($query->id, $query->bank_receipt);
                        return $action;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= accept_html('currencyDepositAccept',encrypt($query->unique_code));
                    $action .= reject_html_get_reject_note('currencyDepositReject',encrypt($query->unique_code));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['actions','bank_receipt'])
                ->make(true);
        }
    }

    public function currencyDepositAcceptList(Request $request)
    {

        if ($request->ajax()) {
            $deposit_list = CurrencyDeposit::where('status',STATUS_ACCEPTED)->select('currency_deposits.id',
                                'currency_deposits.unique_code'
                                , 'currency_deposits.user_id'
                                , 'currency_deposits.wallet_id'
                                , 'currency_deposits.payment_method_id'
                                , 'currency_deposits.currency'
                                , 'currency_deposits.currency_amount'
                                , 'currency_deposits.coin_amount'
                                , 'currency_deposits.rate'
                                , 'currency_deposits.status'
                                , 'currency_deposits.bank_id'
                                , 'currency_deposits.bank_receipt'
                            )->orderBy('currency_deposits.id', 'desc');

            return datatables()->of($deposit_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('to_wallet', function ($query) {
                    return isset($query->wallet)?$query->wallet->name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return isset($query->bank)?$query->bank->bank_name:'N/A';
                })
                ->addColumn('payment_method', function ($query) {
                    return isset($query->payment->payment_method) ? currencyDepositPaymentMethod($query->payment->payment_method) : __("Not Found");
                })
                ->addColumn('bank_receipt', function ($query) {
                    if(!empty($query->bank_receipt)) {
                        $action = modal_image_show($query->id, $query->bank_receipt);
                        return $action;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['bank_receipt'])
                ->make(true);
        }
    }
    public function currencyDepositRejectList(Request $request)
    {

        if ($request->ajax()) {
            $deposit_list = CurrencyDeposit::where('status',STATUS_REJECTED)->select('currency_deposits.id',
                                'currency_deposits.unique_code'
                                , 'currency_deposits.user_id'
                                , 'currency_deposits.wallet_id'
                                , 'currency_deposits.payment_method_id'
                                , 'currency_deposits.currency'
                                , 'currency_deposits.currency_amount'
                                , 'currency_deposits.coin_amount'
                                , 'currency_deposits.rate'
                                , 'currency_deposits.status'
                                , 'currency_deposits.bank_id'
                                , 'currency_deposits.bank_receipt'
                            )->orderBy('currency_deposits.id', 'desc');

            return datatables()->of($deposit_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('to_wallet', function ($query) {
                    return isset($query->wallet)?$query->wallet->name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return isset($query->bank)?$query->bank->bank_name:'N/A';
                })
                ->addColumn('payment_method', function ($query) {
                    return currencyDepositPaymentMethod($query->payment->payment_method);
                })
                ->addColumn('bank_receipt', function ($query) {
                    if(!empty($query->bank_receipt)) {
                        $action = modal_image_show($query->id, $query->bank_receipt);
                        return $action;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['bank_receipt'])
                ->make(true);
        }
    }

    public function currencyDepositAccept($id)
    {
        if (isset($id)) {
            try{
                $response = $this->service->currencyDepositAcceptProcess($id);
                if($response['success'])
                {
                    return redirect()->back()->with('success', __('Pending deposit accepted successfully.'));
                }
                return redirect()->back()->with('dismiss', __('Something went wrong'));
            } catch(\Exception $e)
            {
                storeException("currencyDepositList",$e->getMessage());
                return redirect()->back()->with('dismiss', __('Something went wrong'));
            }
        }
        return redirect()->back()->with('dismiss', __('Something went wrong! Please try again!'));
    }
    public function currencyDepositReject(Request $request)
    {

            try{
                $response = $this->service->currencyDepositRejectProcess($request);
                if($response['success'])
                {
                    return redirect()->back()->with('success', __('Pending deposit rejected successfully.'));
                }
                return redirect()->back()->with('dismiss', __('Something went wrong'));
            } catch(\Exception $e)
            {
                storeException("currencyDepositList",$e->getMessage());
                return redirect()->back()->with('dismiss', __('Something went wrong!'));
            }

    }
}
