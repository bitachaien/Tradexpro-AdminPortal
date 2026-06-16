<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NavbarUpdateRequest extends FormRequest
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
            'id' => 'required|exists:user_navbars',
            'value' => 'string|nullable',
            'type' => 'required|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __("ID is required"),
            'id.exists' => __("ID is invalid"),

           // 'value.required' => __("Value is required"),
            'value.string' => __("Value must be string"),

            'type.required' => __("Type is required"),
            'type.in' => __("Type is invalid"),
        ];
    }
}
