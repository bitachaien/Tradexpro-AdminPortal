<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FutureTradeUpdateProfitLossRequest extends FormRequest
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
            'order_uid'=>'required',
            'take_profit'=>'numeric|gt:0',
            'stop_loss'=>'numeric|gt:0'
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'order_uid.required' => __('Order UID is required!'),
            'take_profit.gt' => __('Take profit price must be greater than 0!'),
            'take_profit.numeric' => __('Take profit is invalid!'),
            'stop_loss.gt' => __('Stop Loss price must be greater than 0!'),
            'stop_loss.numeric' => __('Stop Loss is invalid!')
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
