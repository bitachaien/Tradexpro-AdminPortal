<?php

namespace App\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ApiWhiteListRequest extends FormRequest
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
        return [
            "ip" => [
                "required",
                "regex:/^([0-9]{1,3}.)([0-9]{1,3}.)([0-9]{1,3}.)([0-9]{1,3})$/"
            ],
            "trade" => "required|in:0,1",
            "withdrawal" => "required|in:0,1",
            "status" => "required|in:0,1",
        ];
    }

    public function messages()
    {
        return [
            "ip.required" => __("IP address is required"),
            "ip.regex" => __("IP address is invalid"),

            "trade.required" => __("Trade access status is required"),
            "trade.in" => __("Trade access invalid"),

            "withdrawal.required" => __("Withdrawal access status is required"),
            "withdrawal.in" => __("Withdrawal access invalid"),

            "status.required" => __("Access block status is required"),
            "status.in" => __("Access block invalid"),
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
                'success' => false,
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
