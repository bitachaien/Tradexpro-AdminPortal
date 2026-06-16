<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NexmoRequest extends FormRequest
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
            'nexmo_secret_key'=>'required',
            'nexmo_api_key'=>'required'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'nexmo_secret_key.required' => __("Vonage/Nexmo Secret Key is "),
            'nexmo_api_key.required' => __("Password can't empty")
            
        ];
    }
}
