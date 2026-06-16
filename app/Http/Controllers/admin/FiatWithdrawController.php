<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Services\FiatWithdrawalService;
use App\Model\CurrencyDepositPaymentMethod;
use App\Model\FiatWithdrawal;
use Illuminate\Http\Request;

class FiatWithdrawController extends Controller
{
    public function __construct(){}

    public function fiatWithdrawList()
    {
        try {
            $data['title'] = __('Fiat Withdraw');

            return view('admin.fiat-withdraw.pending-withdraw-list', $data);

        } catch (\Exception $e) {
            storeException("fiatWithdrawController", $e->getMessage());
        }
    }


    public function fiatWithdrawPendingList(Request $request)
    {

        if ($request->ajax()) {
            $withdrawal_list = FiatWithdrawal::where('status',STATUS_PENDING)->with('wallet')->orderBy('fiat_withdrawals.id', 'desc');

            return datatables()->of($withdrawal_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return bankShowHtml($query->bank ?? false);
                })
                ->addColumn('created_at', function ($query) {
                    return $query->created_at;
                })
                ->addColumn('currency_amount', function ($query) {
                    return $query->currency_amount.' '.$query->currency;
                })
                ->addColumn('coin_amount', function ($query) {
                    return $query->coin_amount.' '.$query->wallet?->coin_type;
                })
                ->addColumn('rate', function ($query) {
                    return $query->rate.' '.$query->currency;
                })
                ->addColumn('fees', function ($query) {
                    return $query->fees;
                })
                ->addColumn('status', function ($query) {
                    return deposit_status($query->status);
                })
                ->addColumn('actions', function ($query) {
                    $action = '<div class="activity-icon"><ul>';
                    $action .= html_form_send('fiatWithdrawAccept',encrypt($query->id));
                    $action .= reject_html_get_reject_note('fiatWithdrawReject',encrypt($query->id));
                    $action .= '</ul> </div>';

                    return $action;
                })
                ->rawColumns(['actions','bank','fees', 'status'])
                ->make(true);
        }
    }

    public function fiatWithdrawRejectList(Request $request)
    {

        if ($request->ajax()) {
            $withdrawal_list = FiatWithdrawal::where('status',STATUS_REJECTED)->orderBy('fiat_withdrawals.id', 'desc');

            return datatables()->of($withdrawal_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return bankShowHtml($query->bank ?? false);
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
                ->addColumn('rate', function ($query) {
                    return $query->rate.' '.$query->currency;
                })
                ->addColumn('fees', function ($query) {
                    return $query->fees;
                })
                ->addColumn('status', function ($query) {
                    return deposit_status($query->status);
                })
                ->rawColumns(['bank','fees', 'status'])
                ->make(true);
        }
    }

    public function fiatWithdrawActiveList(Request $request)
    {

        if ($request->ajax()) {
            $withdrawal_list = FiatWithdrawal::where('status',STATUS_ACTIVE)->orderBy('fiat_withdrawals.id', 'desc');

            return datatables()->of($withdrawal_list)
                ->addColumn('user', function ($query) {
                    return isset($query->user)?$query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                })
                ->addColumn('bank', function ($query) {
                    return bankShowHtml($query->bank ?? false);
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
                ->addColumn('rate', function ($query) {
                    return $query->rate.' '.$query->currency;
                })
                ->addColumn('image', function ($query) {
                    return imageshowhtml($query->id,asset(IMG_SLEEP_VIEW_PATH.$query->bank_slip));
                })
                ->addColumn('fees', function ($query) {
                    return $query->fees;
                })
                ->addColumn('status', function ($query) {
                    return deposit_status($query->status);
                })
                ->rawColumns(['bank','fees','image', 'status'])
                ->make(true);
        }
    }

    public function fiatWithdrawAccept(Request $request){
        try{
            $service = new FiatWithdrawalService();
            $response = $service->fiatWithdrawalAdminAccept($request);
            if($response['success'])
                return redirect()->back()->with('success',$response['message']);
            return redirect()->back()->with('dismiss',$response['message']);
        }catch (\Exception){
            return redirect()->back()->with('dismiss',__("Something went wrong"));
        }
    }
    public function fiatWithdrawReject(Request $request){ redirect()->route('fiatWithdrawList');
        try{
            $service = new FiatWithdrawalService();
            $response = $service->fiatWithdrawalAdminReject($request);
            if($response['success'])
                return redirect()->route('fiatWithdrawList')->with('success',$response['message']);
            return redirect()->route('fiatWithdrawList')->with('dismiss',$response['message']);
        }catch (\Exception){
            return redirect()->route('fiatWithdrawList')->with('dismiss',__("Something went wrong"));
        }
    }

    public function getWithdrawlPaymentMethod()
    {
        $data['title'] = __('Payment Method List');
        $data['payment_methods'] = CurrencyDepositPaymentMethod::where('type', 'fiat-withdrawl')->get();;
        return view('admin.fiat-withdraw.payment-method-list', $data);
    }

    public function getWithdrawlPaymentMethodAdd()
    {
        $data['title'] = __('Add New Payment Method');
        $data['payment_methods'] = currencyWithdrawalPaymentMethod();
        return view('admin.fiat-withdraw.payment-method-addEdit', $data);
    }

    public function getWithdrawlPaymentMethodEdit($id)
    {
        $data['title'] = __('Update Payment Method');

        $paymentMethodDetails = CurrencyDepositPaymentMethod::find($id);
        if(isset($paymentMethodDetails))
        {
            $data['item'] = $paymentMethodDetails;
            $data['payment_methods'] = currencyWithdrawalPaymentMethod();

            return view('admin.fiat-withdraw.payment-method-addEdit', $data);
        }else {
            return redirect()->back()->with("success",__('Payment Method not found!'));
        }
    }
}
