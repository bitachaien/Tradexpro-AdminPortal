<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ];
        $user = User::where('email', $this->email)->first();
        if (isset($user)) {
            if (isset(allsetting()['select_captcha_type']) && (allsetting()['select_captcha_type'] == CAPTCHA_TYPE_RECAPTCHA)) {
                $rules['g-recaptcha-response'] = 'required|captcha';
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
        return [
            'email.required' => __("Email address can't empty"),
            'password.required' => __("Password can't empty"),
            'email.email' => __('Invalid email address.'),
            'email.exists' => __('Email address doesn\'t exist.'),
            'lot_number.required' => __('please, click to verify!'),
            'captcha_output.required' => __('please, click to verify!'),
            'pass_token.required' => __('please, click to verify!'),
            'gen_time.required' => __('please, click to verify!'),
        ];
    }
}
