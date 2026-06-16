<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyWithdrawalAcceptRequest extends FormRequest
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
            'id' => 'required',
            'receipt' => "required|image|mimes:jpg,png,jpeg,JPG,PNG,webp,gif|max:$file_size"
        ];
    }

    public function messages()
    {
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2);
        $messages=[
            'id.required' => __("Withdrawal id is required"),
            'receipt.required' => __("Bank receipt id is required"),
            'receipt.image' => __("Bank receipt must be image file"),
            'receipt.mimes' => __("Bank receipt must be jpg,png,jpeg,JPG,PNG file"),
            'receipt.max' => __("Bank receipt maximum size is :size MB", ["size" => $file_size]),
        ];

        return $messages;
    }
}
