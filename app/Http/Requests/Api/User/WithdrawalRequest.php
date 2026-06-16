<?php

namespace App\Http\Requests\Api\User;

use App\Model\Wallet;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'address' => ['required', 'string'],
            'wallet_id' => 'required',
        ];
        $settings = settings();
        if(filter_var($settings["two_factor_withdraw"],FILTER_VALIDATE_BOOLEAN) && !defined("IS_PUBLIC_API")){
            $rule['code'] = ['required'];
//            $rule['code_type'] = ['required'];
        }
        if ($this->wallet_id) {
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $this->wallet_id])
                ->select('coins.*')->first();
            if ($wallet) {
                $rule['amount'] = 'required|numeric|min:' . $wallet->minimum_withdrawal . '|max:' . $wallet->maximum_withdrawal;
                if (!empty($this->note)) {
                    $rule['note'] = 'string';
                }
                if ($wallet->coin_type == COIN_USDT && $wallet->network == COIN_PAYMENT) {
                    $rule['network_type'] = 'required';
                }
            }
        }

        return $rule;
    }

    public function messages()
    {
        $msg = [
            'address.required' => __('Address is required'),
            'address.string' => __('Address must be a string!'),
            'amount.required' => __('Coin type is required'),
            'amount.numeric' => __('Amount must be numeric field!'),
            'code.required' => __('Code is required'),
            'code_type.required' => __('Code Type is required'),
            'wallet_id.required' => __('Wallet is required'),
        ];
        if (!empty($this->message)) {
            $msg['note.string'] = __('Message must be a string');
        }

        return $msg;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = ['success'=>false,
                'data'=>[],
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}
