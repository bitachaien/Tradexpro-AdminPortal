<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResendVerificationEmailCodeRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'email.required' => __("Email address can't empty"),
            'email.email' => __('Invalid email address.'),
            'email.exists' => __('Email address doesn\'t exist.')
        ];
    }
}
