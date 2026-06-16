<?php

namespace App\Http\Validators;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StopLimitValidators extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public $coin_type;

    public function authorize()
    {
        return Auth::user()->status == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $coinSetting = getService(['method'=>'getCoin','params'=>['id'=>$this->trade_coin_id]]);
        $minimum_amount = 0;
        if ($coinSetting) {
            $this->coin_type = $coinSetting[0]['coin_type'];

            $route = Route::current()->action;
            $controllerMethod = $route['as'] ?? '';
            if  ($controllerMethod == 'placeStopLimitSellOrderApp') {
                $minimum_amount = $coinSetting[0]['minimum_sell_amount'];
            } elseif ($controllerMethod == 'placeBuyStopLimitOrderApp') {
                $minimum_amount = $coinSetting[0]['minimum_buy_amount'];
            }
        }

        return [
            'stop' => 'required|numeric|between:0.00000001,99999999999.99999999',
            'limit' => 'required|numeric|between:0.00000001,99999999999.99999999',
            'amount' => "required|numeric|gt:0|between:$minimum_amount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(),'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'stop.required' => __('Stop field can not be Empty'),
            'stop.between' => __('Invalid number for Stop Limit stop'),
            'stop.numeric' => __('Stop Field Must be Numeric Value'),
            'limit.required' => __('Limit field can not be Empty'),
            'limit.between' => __('Limit Must be more than 0.00000001'),
            'limit.numeric' => __('Limit field Must be Numeric Value'),
            'amount.required' => __('Amount field can not be Empty'),
            'amount.numeric' => __('Amount value is invalid!'),
            'amount.between' => __('Minimum amount of :ctype should be :min!', ['ctype' => $this->coin_type]),
            'amount.gt' => __("Amount should not be zero"),

            'trade_coin_id.required' => __('Coin type field is required.'),
            'base_coin_id.required' => __('Base coin field is required.')
        ];

        return $message;
    }

    protected function failedValidation(Validator $validator)
    {
//        if ($this->header('accept') == "application/json") {
            $errors = '';
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors = $errors . $error . "\n";
                }
            }
            $json = [
                'status' => false,
                'message' => $errors
            ];

            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
//        } else {
//            throw (new ValidationException($validator))
//                ->errorBag($this->errorBag)
//                ->redirectTo($this->getRedirectUrl());
//        }
    }
}
