<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GiftCardCategoryRequest extends FormRequest
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
            'uid'    => 'exists:gift_card_categories',
            'name'   => 'required',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(){
        return [
            'uid.exists' => __("Category uid in invalid"),

            'name.required' => __("Category name can not be empty"),

            'status.required' => __("Category status is required"),
            'status.in' => __("Category status is invalid"),
        ];
    }
}
