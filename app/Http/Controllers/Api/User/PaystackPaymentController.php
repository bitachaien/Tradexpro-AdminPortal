<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaystackPaymentURLRequest;
use App\Model\CurrencyList;
use Illuminate\Http\Request;

class PaystackPaymentController extends Controller
{
    public function getPaystackPaymentURL(PaystackPaymentURLRequest $request)
    {
        $url = "https://api.paystack.co/transaction/initialize";

        $secret_key = allsetting('PAYSTACK_SECRET');

        $query = "";
        if(isset($request->crypto_type) && $request->crypto_type == CURRENCY_TYPE_FIAT){
            if(!isset($request->currency))
            {
                $response = ['success'=>false, 'message'=>__('Currency is required!')];
                return response()->json($response);
            }
            if(!isset($request->wallet_id))
            {
                $response = ['success'=>false, 'message'=>__('Wallet ID is required!')];
                return response()->json($response);
            }

            $query = '?coin_type='.$request->currency.'&crypto_type='.$request->crypto_type.'&wallet_id='.$request->wallet_id.'&payment_method_id='.$request->payment_method_id.'&amount='.$request->amount;
        }else{
            if(!isset($request->wallet_id))
            {
                $response = ['success'=>false, 'message'=>__('Wallet ID is required!')];
                return response()->json($response);
            }
            $query = '?coin_type=&crypto_type=1&wallet_id='.$request->wallet_id.'&payment_method_id='.$request->payment_method_id.'&amount='.$request->amount;
        }
        
        $callback_url = allsetting('exchange_url').'/verify-paystack'.$query;

        $amount = 0;
        if(isset($request->crypto_type) && $request->crypto_type == CURRENCY_TYPE_FIAT){
            $amount = $request->amount;
        }else{
            $currency_ZAR = CurrencyList::where('code','ZAR')->first();
            $currency_rate_ZAR = isset($currency_ZAR)? $currency_ZAR->rate:1;
            $converted_amount = $request->amount * $currency_rate_ZAR;
            $amount =str_replace('.', '', number_format($converted_amount, 2, '.', ''));
        }
        

        $fields = [
          'email' => $request->email,
          'amount' => $amount,
          'callback_url'=>$callback_url
        ];
      
        $fields_string = http_build_query($fields);
        
        
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer ".$secret_key,
          "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);

        $result_json_data = json_decode($result);
        $data = [];
        if($result_json_data->status)
        {
            $data['authorization_url'] = $result_json_data->data->authorization_url;
            $data['reference'] = $result_json_data->data->reference;
            $response = ['success'=>true, 'message'=>__('Authorization URL created'), 'data'=>$data];
        }else{
            $response = ['success'=>false, 'message'=>__('Authorization URL created is failed')];
        }
        
        return response()->json($response);
    }

    public function verificationPaystackPayment(Request $request)
    {
        if(isset($request->reference))
        {
            $secret_key = allsetting('PAYSTACK_SECRET');
            $curl = curl_init();
  
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$request->reference,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$secret_key,
                "Cache-Control: no-cache",
              ),
            ));
            
            $result = curl_exec($curl);
            $err = curl_error($curl);
          
            curl_close($curl);

            $result_json_data = json_decode($result);

            if($result_json_data->status)
            {
                $response = ['success'=>true, 'message'=>$result_json_data->message];
            }else{
                $response = ['success'=>false, 'message'=>$result_json_data->message];
            }
        }else{
            $response = ['success'=>false, 'message'=>__('Reference field is required!')];
        }

        return response()->json($response);
    }

}
