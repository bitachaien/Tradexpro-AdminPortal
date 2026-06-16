<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeoRequest extends FormRequest
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
            'meta_keywords' => 'required|max:255',
            'meta_description' => 'required|max:255',
            'social_title' => 'required|max:255',
            'social_description' => 'required|max:255',
            
        ];
        $old_img = allsetting('seo_image');
        if(empty($old_img))
        {
            $rules =[
                'image'=>'required|mimes:jpg,png,jpeg,JPG,PNG|max:2048'
            ];
        }
        return $rules;
    }

    public function messages()
    {
        $messages = [
            'meta_keywords.required' => __('Meta Keyword is required.'),
            'meta_description.required' => __('Meta Description is required.'),
            'social_title.required' => __('Social Title is required.'),
            'social_description.required' => __('Social Description is required.'),
            'image.required' => __('Image is required.'),
            'image.mimes' => __('Image must be jpg or png.'),
        ];
        return $messages;
    }
}
