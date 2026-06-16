<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GiftCardBannerRequest extends FormRequest
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
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
        return [
            'uid'         => 'exists:gift_card_banners',
            'title'       => 'required',
            'sub_title'   => 'required',
            'category_id' => 'required|exists:gift_card_categories,uid',
            'status'      => 'required|in:0,1',
            'banner'      => "required_without:uid|image|mimes:png,jpg,gif,jpeg|max:$file_size",
        ];
    }

    /**
     * Get the validation message that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2);
        return [
            'uid.exists'              => __("Banner is invalid"),

            'title.required'          => __("Title is required"),

            'sub_title.required'      => __("Sub title is required"),

            'category_id.required'    => __("Category is required"),
            'category_id.exists'      => __("Category is invalid"),

            'status.required'         => __("Status is required"),
            'status.in'               => __("Status is invalid"),
            
            'banner.required_without' => __("Banner is required"),
            'banner.image'            => __("Banner must be an image"),
            'banner.mimes'            => __("Banner must be type of 'png,jpg,gif,jpeg'"),
            'banner.max'              => __("Banner maximum size is :size MB", ["size" => $file_size]),
        ];
    }
}
