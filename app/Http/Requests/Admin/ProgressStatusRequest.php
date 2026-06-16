<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgressStatusRequest extends FormRequest
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
            'title' => 'required',
            'progress_type_id' => 'required',
            'status' => 'required',
            'description' => 'required',
            
            
        ];
        
        return $rules;
    }

    public function messages()
    {
        $messages = [
            'title.required' => __('Title is required.'),
            'progress_type_id.required' => __('Progress Type is required.'),
            'status.required' => __('Status is required.'),
            'description.required' => __('Description is required.'),
            
        ];
        return $messages;
    }
}
