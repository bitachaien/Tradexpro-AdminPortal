<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositRequest extends FormRequest
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
        $rules=[
            'network' => 'required',
            'transaction_id' => 'required',
            'coin_type' => 'required|exists:coins,coin_type',
            'type' => 'required'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'network.required' => __('Coin API is required'),
            'transaction_id.required' => __('Transaction Id is required'),
            'coin_type.required' => __('Coin Type is required'),
        ];
    }
}
