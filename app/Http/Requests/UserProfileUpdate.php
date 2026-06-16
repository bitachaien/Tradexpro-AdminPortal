<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserProfileUpdate extends FormRequest
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
        $rule = [
          //  'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nickname' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'numeric'],
        ];
        if (Auth::user()->role == USER_ROLE_USER) {
            $rule['country'] = ['required'];
            $rule['gender'] = ['required'];
        }
        if(!empty($this->id))
        {
            $rule['nickname'] = 'unique:users,nickname,'.decrypt($this->id);
        }else{
            $rule['nickname'] = 'unique:users,nickname,'.Auth::user()->id;
        }
        if(Auth::user()->role == USER_ROLE_ADMIN && !empty($this->email))
        {
            $rule['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email,'.Auth::user()->id];
        }
        return $rule;
    }

    public function messages()
    {
        return  [
            'nickname.required' => __('Nick name can not be empty!'),
            'nickname.unique' => __('This Nick name already taken!'),
            'first_name' => __('First name can not be empty'),
            'phone.required' => __('Phone number can not be empty'),
            'country.required' => __('Country can not be empty'),
            'phone.numeric' => __('Please enter a valid phone number'),
            'last_name' => __('Last name can not be empty'),
            'gender' => __('Gender can not be empty')
            ];
    }
}
