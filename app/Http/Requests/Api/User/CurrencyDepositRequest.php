<?php

namespace App\Http\Requests\Api\User;

use App\Model\CurrencyDepositPaymentMethod;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CurrencyDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user() ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'payment_method_id' => 'required',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|gt:0',
        ];
        if ($this->payment_method_id == 'pay_stack') {

        } else {
            $rule['payment_method_id'] = 'exists:currency_deposit_payment_methods,id';
        }
        $paymentMethod = CurrencyDepositPaymentMethod::where(['id' => $this->payment_method_id, 'status' => STATUS_SUCCESS])->first();
        if ($paymentMethod) {
            if ($paymentMethod->payment_method == WALLET_DEPOSIT) {
                $rule['from_wallet_id'] = 'required|exists:wallets,id';
            } else {
                $rule['currency'] = 'required|exists:currency_lists,code';
            }
            if ($paymentMethod->payment_method == BANK_DEPOSIT) {
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rule['bank_id'] = 'required|exists:banks,id';
                $rule['bank_receipt'] = "required|image|mimes:jpg,png,jpeg,JPG,PNG,webp,gif|max:$file_size";
            }
            if ($paymentMethod->payment_method == STRIPE) {
                $rule['stripe_token'] = 'required';
            }
            if ($paymentMethod->payment_method == PAYPAL) {
                $rule['paypal_token'] = 'required';
            }
        }
        return $rule;

    }

    public function messages()
    {
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2);
        return [
            'payment_method_id.required' => __('Payment method is required'),
            'payment_method_id.exists' => __('Payment method does not exists'),
            'wallet_id.required' => __('Wallet is required'),
            'wallet_id.exists' => __('Invalid wallet'),
            'from_wallet_id.required' => __('From wallet is required'),
            'from_wallet_id.exists' => __('Invalid from wallet'),
            'currency.required' => __('Currency is required'),
            'currency.exists' => __('Invalid currency'),
            'amount.required' => __('Amount is required'),
            'amount.numeric' => __('Amount must be number'),
            'amount.gt' => __('Amount must be greater than 0'),
            'bank_id.required' => __('Bank is required'),
            'bank_id.exists' => __('Invalid bank'),
            'bank_receipt.required' => __('Bank receipt is required'),
            'bank_receipt.max' => __('Bank receipt must be :size MB',["size" => $file_size]),
        ];
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
