<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SendGiftCardRequest extends FormRequest
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
            'card_uid' => 'required|exists:gift_cards,uid',
            'send_by' => 'required|in:1,2',
            'to_email' => 'email',   //required_without:to_phone|
            'to_phone' => 'numeric', //required_without:to_email|
        ];
    }

    public function messages()
    {
        return [
            'send_by.required' => __("Sending type is required"), 
            'send_by.in' => __("Sending type is invalid"), 

            'to_email.required_without' => __("Email address is required"),
            'to_email.email' => __("Email address is invalid"),
            
            'to_phone.required_without' => __("Phone is required"),
            'to_phone.numeric' => __("Phone is invalid"),
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
