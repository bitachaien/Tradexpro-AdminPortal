<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdminRequest extends FormRequest
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
            'first_name' => 'required|string',
            'last_name' => 'required|string|min:1',
            'email' => 'required_without:id|email:rfc,dns|unique:users',
            'phone' => 'required|regex:/^[0-9]+$/',
            'role' => 'required|exists:roles,id'
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('First Name is required'), 
            'first_name.string' => __('First Name must be string'), 
        
            'last_name.required' => __('Last Name is required'), 
            'last_name.string' => __('Last Name must be string'), 

            'email.required_without' => __('Email is required'),
            'email.email' => __('Email is invalid'),
            'email.unique' => __('User with this email already exists'),

            'phone.required' => __('Phone is required'),
            'phone.regex' => __('Phone is invalid'),

            'role.required' => __('Role is required'),
            'role.exists' => __('Role is invalid'),
        ];
    }
}
