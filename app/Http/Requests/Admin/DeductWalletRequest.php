<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeductWalletRequest extends FormRequest
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
            'deduct_amount'=>'required|numeric|gt:0',
            'reason' => 'required|string'
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            "deduct_amount.required" => __("Please, set the deduct balance"),
            "deduct_amount.numeric" => __("Deduct Balance must be number"),
            "deduct_amount.gt" => __("Deduct Balance must be greater than 0"),
            "reason.required" => __("Please, Input the reason of deduct balance"),
            "reason.string" => __("Reason must be string")
        ];
    }
}
