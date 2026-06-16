<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddPermissionRoute extends FormRequest
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
            'action' => 'required|string',
            'for' => 'required|string',
            'route' => 'required|string',
            'group' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'action.required' => __('Action field is required'),
            'action.string' => __('Action field must be string'),

            'for.required' => __('Details field is required'),
            'for.string' => __('Details field must be string'),

            'route.required' => __('Route field is required'),
            'route.string' => __('Route field must be string'),

            'group.required' => __('Group field is required'),
            'group.string' => __('Group field must be string'),
        ];
    }
}
