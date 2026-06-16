<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StakingInvestmentRequest extends FormRequest
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
            'uid'=>'required|exists:staking_offers,uid',
            'amount'=>'required|numeric|gt:0',
            'auto_renew_status'=>'required|in:'.STAKING_IS_NOT_AUTO_RENEW.','.STAKING_IS_AUTO_RENEW,
        ];
        return $rules;
    }

    public function messages()
    {
        $messages = [
            'uid.required' => __('Offer Details UID is missing!'),
            'uid.exists' => __('Invalid Offer details UID!'),
            'amount.required' => __('Please, enter the amount you want to invest'),
            'amount.numeric' => __('Amount must be numeric!'),
            'amount.gt' => __('Amount must be greater than 0!'),
            'auto_renew_status.required' => __('Auto renew status is missing!'),
            'auto_renew_status.in' => __('Auto renew status is invalid!'),
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
