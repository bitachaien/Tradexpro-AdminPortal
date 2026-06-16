<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CoinSwapRequest extends FormRequest
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
        $settings = settings();
        $rule = [
            'from_coin_id' => 'required|exists:wallets,id',
            'to_coin_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|gt:0',
        ];
        if(filter_var($settings["two_factor_swap"],FILTER_VALIDATE_BOOLEAN) && $this->isMethod('post')){
           // $rule['code'] = ['required'];
           // $rule['code_type'] = ['required'];
        }
        return $rule;
    }
    public function messages()
    {
        return [
            'from_coin_id.required' => __("From wallet id is required"),
            'to_coin_id.required' => __("To wallet id is required"),
            'code.required' => __('Code is required'),
            'code_type.required' => __('Code Type is required'),
            'amount.required' => __('Amount is required'),
            'amount.gt' => __('Amount must be greater than 0'),
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
            $json = [
                'success'=>false,
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
