<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class TransactionDepositRequest extends FormRequest
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
        return [
            "coin_id" => "required|numeric|exists:coins,id",
            "network_id"=> "required|numeric",
            "trx_id"    => "required|string",
        ];
    }

    public function messages()
    {
        return [
            "coin_id.required" => __("Coin is required"),
            "coin_id.numeric"   => __("Coin is invalid"),
            "coin_id.exists"   => __("Coin not exist"),

            "network_id.required" => __("Network is required"),
            "network_id.numeric" => __("Network is invalid"),

            "trx_id.required" => __("Transaction hash is required"),
            "trx_id.numeric" => __("Transaction hash must be a string"),
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
