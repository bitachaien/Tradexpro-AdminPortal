<?php

namespace App\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ChangePasswordRequest extends FormRequest
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
            'old_password' => 'required',
            'password' =>[
                'required',
                'string',
                'min:8',
                'strong_pass',// must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
            ],
            'password_confirmation' => 'required|min:8|same:password'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'old_password.required' => __("Old password is required"),

            'password.required' => __("New password is required"),
            'password.string' => __("New password must be string"),
            'password.min' => __("New password must be at least 8 characters"),
            'password.strong_pass' => __("Password must consist of at least one uppercase letter, one lowercase letter and one number."),
            'password.regex' => __("Password must consist of at least one uppercase letter, one lowercase letter and one number."),

            'password_confirmation.required' => __("Confirmation password is required"),
            'password_confirmation.min' => __("Confirmation password must be at least 8 characters"),
            'password_confirmation.same' => __("Confirmation password must be same as new password"),
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
