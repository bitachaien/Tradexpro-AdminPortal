<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Model\CurrencyDepositPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CurrencyWalletDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|exists:currency_lists,code',
        ];

        $paymentMethod = CurrencyDepositPaymentMethod::where(['id' => $this->payment_method_id, 'status' => STATUS_SUCCESS])->first();
        if ($paymentMethod) {
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
        return [
            'payment_method_id.required' => __('Payment method is required'),
            'payment_method_id.exists' => __('Payment method does not exists'),
            'currency.required' => __('Currency is required'),
            'currency.exists' => __('Invalid currency'),
            'amount.required' => __('Amount is required'),
            'amount.numeric' => __('Amount must be number'),
            'amount.gt' => __('Amount must be greater than 0'),
            'bank_id.required' => __('Bank is required'),
            'bank_id.exists' => __('Invalid bank'),
            'bank_receipt.required' => __('Bank receipt is required'),
            'stripe_token.required' => __('Stripe token is required'),
            'paypal_token.required' => __('Paypal token is required'),
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
