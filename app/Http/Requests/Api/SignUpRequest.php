<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SignUpRequest extends FormRequest
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
        $rules =[
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['numeric', 'unique:users,phone'],
            'password' =>[
                'required',
                'strong_pass',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
            ],
            'password_confirmation' => 'required|min:8|same:password',
        ];
        $agent = checkUserAgent($this);
        if($agent == 'android' || $agent == 'ios') {
        } else {
            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_RECAPTCHA)) {
                $rules['recapcha'] = 'required|captcha';
            }

            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_GEETESTCAPTCHA)) {
                $rules['lot_number'] = 'required';
                $rules['captcha_output'] = 'required';
                $rules['pass_token'] = 'required';
                $rules['gen_time'] = 'required';
            }
        }
        return $rules;
    }

    public function messages()
    {
        return  [
            'first_name.required' => __('First name can not be empty'),
            'last_name.required' => __('Last name can not be empty'),
            'password.required' => __('Password field can not be empty'),
            'password_confirmation.required' => __('Confirm Password field can not be empty'),
            'password.min' => __('Password length must be at least 8 characters.'),
            'password.regex' => __('Password must consist of at least one uppercase letter, one lowercase letter and one number.'),
            'password.strong_pass' => __('Password must consist of at least one uppercase letter, one lowercase letter and one number.'),
            'password_confirmation.min' => __('Confirm Password length must be at least 8 characters.'),
            'password_confirmation.same' => __('Password and confirm password doesn\'t match'),
            'email.required' => __('Email field can not be empty'),
            'email.unique' => __('Email Address already exists'),
            'email.email' => __('Invalid email address'),
            // 'phone.exists' => __('Phone number already exists'),
            'phone.numeric' => __('Phone number invalid')
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
