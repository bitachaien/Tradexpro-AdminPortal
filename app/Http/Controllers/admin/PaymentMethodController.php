<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\PaymentMethodService;

class PaymentMethodController extends Controller
{
    private $paymentMethodService;

    public function __construct()
    {
        $this->paymentMethodService = new PaymentMethodService();
    }

    public function currencyPaymentMethod()
    {
        $data['title'] = __('Payment Method List');
        $data['currency_deposit_payment_methods'] = $this->paymentMethodService->getCurrencyDepositePaymentMethods();
        return view('admin.payment-method.list', $data);
    }

    public function currencyPaymentMethodAdd()
    {
        $data['title'] = __('Add New Payment Method');
        $data['payment_methods'] = currencyDepositPaymentMethod();
        return view('admin.payment-method.addEdit', $data);
    }

    public function currencyPaymentMethodStore(Request $request)
    {
        $response = $this->paymentMethodService->savePaymentMethod($request);

        if ($response['success'] == true) {
            if($request->type == 'fiat-deposit')
            {
                return redirect()->route('currencyPaymentMethod')->with(['success'=> $response['message']]);
            }else{
                return redirect()->route('getWithdrawlPaymentMethod')->with(['success'=> $response['message']]);
            }
            
        } else {
            return redirect()->back()->with(['dismiss'=> $response['message']]);
        }
    }

    public function currencyPaymentMethodStatus(Request $request)
    {
        $response = $this->paymentMethodService->statusChange($request);

        if ($response['success'] == true) {
            return response()->json(['message'=>$response['message']]);
        } else {
            return response()->json(['message'=>$response['message']]);
        }
    }

    public function currencyPaymentMethodDelete($id)
    {
        $response = $this->paymentMethodService->deleteCurrencyPaymentMethod($id);

        if ($response['success'] == true) {
            return redirect()->back()->with("success",__('Deleted successfully'));
        } else {
            return redirect()->back()->with("success",$response['message']);;
        }
    }

    public function currencyPaymentMethodEdit($id)
    {
        $data['title'] = __('Update Payment Method');

        $response = $this->paymentMethodService->getCurrencyDepositePaymentMethod($id);
        if($response['success']==true)
        {
            $data['item'] = $response['item'];
            $data['payment_methods'] = currencyDepositPaymentMethod();

            return view('admin.payment-method.addEdit', $data);
        }else {
            return redirect()->back()->with("success",__('Payment Method not found!'));
        }
    }
}

