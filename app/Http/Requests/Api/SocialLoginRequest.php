<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
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
            "login_type" => "required|in:". implode(",", [
                LOGIN_WITH_APPLE,
                LOGIN_WITH_FACEBOOK,
                LOGIN_WITH_GOOGLE,
                LOGIN_WITH_TWITTER,
            ]),
            // "email"       => "required|email",
            // "name"        => "required",
            // "userID"      => "required",
            "access_token"=> "required",
        ];
    }

    public function messages(){
        return [
            "login_type.required" => __("Social login type missing"),
            "login_type.in"       => __("Invalid social login"),

            "email.required" => __("Email not found in your social account"),
            "email.email"    => __("Email is invalid"),

            "name.required"         => __("Name not found in your social account"),
            "userID.required"       => __("UserID not found"),
            "access_token.required" => __("Access token not found"),
        ];
    }
}
