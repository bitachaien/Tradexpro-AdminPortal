<?php

namespace App\Http\Repositories;


use App\Model\CurrencyDepositPaymentMethod;
use App\Model\CurrencyDeposit;

class PaymentMethodRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getCurrencyDepositePaymentMethods()
    {
        return CurrencyDepositPaymentMethod::where('type', 'fiat-deposit')->get();
    }
    public function savePaymentMethod($data)
    {
        $currencyDepositPayment = CurrencyDepositPaymentMethod::where('payment_method',$data['payment_method'])
                                                                ->where('type', $data['type'])->first();

        if((isset($currencyDepositPayment) && !isset($data['id'])) || (isset($currencyDepositPayment) && (isset($data['id']) && $data['id']!=$currencyDepositPayment->id)) )
        {
            $response = responseData(true,__('You already added this payment method!'));
        }else{
            if(isset($data['id']))
            {
                $paymentMethod = CurrencyDepositPaymentMethod::where('id',$data['id'])->first();
                if ($paymentMethod) {
                    if ($paymentMethod->payment_method != $data['payment_method']) {
                        $currencyDeposit = CurrencyDeposit::join('currency_deposit_payment_methods','currency_deposit_payment_methods.id','=','currency_deposits.payment_method_id')
                            ->where(['currency_deposit_payment_methods.id' => $data['id']])
                            ->first();
                        if(isset($currencyDeposit))
                        {
                            $response = responseData(false,__('Payment method can not be changed, you already used it in Currency deposit.!'));
                        } else {
                            $paymentMethod->update($data);
                            $response = responseData(true,__('Payment method  updated successfully!'));
                        }
                    } else {
                        $paymentMethod->update($data);
                        $response = responseData(true,__('Payment method  updated successfully!'));
                    }
                } else {
                    $response = responseData(false,__('Payment method not found'));
                }
            } else {
                CurrencyDepositPaymentMethod::Create($data);
                $response = responseData(true,__('New payment method added successfully!'));
            }
        }
        return $response;

    }

    public function statusChange($data)
    {
        $currency_payment_method = CurrencyDepositPaymentMethod::where('id',$data['id'])->first();

        if ($currency_payment_method) {
            if ($currency_payment_method->status == 1) {
               $currency_payment_method->update(['status' => 0]);
            } else {
                $currency_payment_method->update(['status' => 1]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function deleteCurrencyPaymentMethod($data)
    {
        $currency_deposit = CurrencyDeposit::where('payment_method_id',$data['id'])->get();
        if(count($currency_deposit)>0)
        {
            $response = responseData(false,__('Payment method can not be deleted, you already use it in Currency deposit.'));
        }else
        {
            $currency_payment_method = CurrencyDepositPaymentMethod::where('id',$data['id'])->first();

            if ($currency_payment_method) {
                $currency_payment_method->delete();
                $response = responseData(true,__('Payment method deleted successfully!'));
            } else {
                $response = responseData(false,__('Payment method is not deleted!'));
            }
        }
        return $response;
    }

    public function getCurrencyDepositePaymentMethod($data)
    {
        $currency_payment_method = CurrencyDepositPaymentMethod::where('id',$data['id'])->first();
        if ($currency_payment_method) {

            return $currency_payment_method;

        } else {

            return null;
        }
    }
}
