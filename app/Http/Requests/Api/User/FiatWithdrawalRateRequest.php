<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FiatWithdrawalRateRequest extends FormRequest
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
        $rules = [
            'wallet_id' => 'required',
            'currency' => 'required|exists:currency_lists,code',
            'amount' => 'required|numeric|gt:0'
        ];

        return $rules;
    }

    public function messages()
    {
        return[
            'wallet_id.required' => __('Wallet is required'),
            'currency.required' => __('Currency is required'),
            'currency.exists' => __('Currency is invalid'),
            'amount.required' => __('Amount is required'),
            'amount.numeric' => __('Amount must be number'),
            'amount.gt' => __('Amount must be greater than 0')
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
