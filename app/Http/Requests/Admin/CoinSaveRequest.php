<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CoinSaveRequest extends FormRequest
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
            'currency_type' => 'required|in:1,2',
            'name' => 'required|max:100',
            'coin_type' => 'required|max:80|unique:coins',
            'get_price_api' => 'required',
            'network' => 'required',
        ];
        if ($this->get_price_api == 2) {
            $rules['coin_price'] = 'required|numeric|gt:0';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'currency_type.required' => __('Currency type is required'),
            'currency_type.in' => __('Currency type is invalid'),
            'coin_type.required' => __('Coin type is required'),
            'coin_type.unique' => __('Coin type already exists'),
            'name.required' => __('Coin full name is required'),
            'coin_price.required' => __('Coin price is required'),
            'coin_price.numeric' => __('Coin price must be number'),
            'network' => __('Coin API is required')
        ];

        return $messages;
    }
}
