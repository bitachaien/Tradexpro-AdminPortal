<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FutureTradeBalanceTransferRequest extends FormRequest
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
            'transfer_from' => 'required|in:'.SPOT_TRADE.','.FUTURE_TRADE,
            'coin_type' => 'required|exists:coins,coin_type',
            'amount' => 'required|numeric|gt:0'
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'transfer_from.required' => __('Transfer From is required!'),
            'transfer_from.in' => __('Transfer From is invalid!'),
            'coin_type.required' => __('Coin type is required!'),
            'coin_type.exists' => __('Coin type is invalid!'),
            'amount.required' => __('Amount is required!'),
            'amount.numeric' => __('Amount must be number!'),
            'amount.gt' => __('Amount must be greater than 0!'),
        ];

        return $messages;
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
            $json = ['success' => false,
                'data' => [],
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
