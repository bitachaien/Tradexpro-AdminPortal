<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransactionExportRequest extends FormRequest
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
            'type' => 'required|in:deposit,withdrawal,pending_deposit,referral,pending_withdrawal_form,reject_withdrawal_form,active_withdrawal_form',
            'export_to' => 'required|in:.csv,.xlsx',
            'from_date' => "required_with:to_date|date|nullable",
            'to_date' => "required_with:from_date|date|after_or_equal:from_date|nullable",

        ];
    }

    public function messages()
    {
        return [
            'type.required' => __("Type is required"),
            'type.in' => __("Type is invalid"),

            'export_to.required' => __("Export file type is required"),
            'export_to.in' => __("Export file type is invalid"),


            'from_date.required_with' => __("From date is Required"),
            'from_date.date' => __("From date is invalid"),

            'to_date.required_with' => __("To date is Required"),
            'to_date.required' => __("To date is invalid"),
            'to_date.after_or_equal' => __("The to date must be after or equal with :from_date",["from_date" => $this->from_date ?? __("form date")]),
        ];
    }
}
