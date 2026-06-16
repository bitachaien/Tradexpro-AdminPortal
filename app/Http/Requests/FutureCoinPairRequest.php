<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FutureCoinPairRequest extends FormRequest
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
            'id'=>'required',
            'maintenance_margin_rate'=>'required|numeric|gt:-1',
            'leverage_fee'=>'gt:-1|numeric',
            'max_leverage'=>'required|numeric|gt:0',
            'bot_operation' => 'required',
            'bot_percentage' => 'gte:0|numeric',
            'upper_threshold' => 'gte:0|numeric',
            'lower_threshold' => 'gte:0|numeric',
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'id.required' => __('Invalid Request!'),
            'maintenance_margin_rate.required' => __('Enter Maintenance Margin Rate!'),
            'maintenance_margin_rate.numeric' => __('Maintenance Margin Rate must be number!'),
            'maintenance_margin_rate.gt' => __('Maintenance Margin Rate can not be negative!'),
            'leverage_fee.gt' => __('Leverage Fee can not be negative!'),
            'leverage_fee.numeric' => __('Leverage Fee must be number!'),
            'max_leverage.required' => __('Enter Max Leverage Rate!'),
            'max_leverage.numeric' => __('Max Leverage must be number!'),
            'max_leverage.gt' => __('Max Leverage must be greater than zero!'),
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
