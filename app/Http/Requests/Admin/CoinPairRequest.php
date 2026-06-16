<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CoinPairRequest extends FormRequest
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
            'parent_coin_id' => 'required|exists:coins,id',
            'child_coin_id' => 'required|exists:coins,id',
        ];
        if (empty($this->edit_id)) {
            $rules['get_price_api'] = 'required';
            if($this->get_price_api == 2) {
                $rules['price'] = 'required|numeric|gt:0';
            }
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'parent_coin_id.required' => __('Must be select a base coin'),
            'child_coin_id.required' => __('Must be select a pair coin'),
            'price.required' => __('Initial price is required'),
            'price.numeric' => __('Initial price should be numeric'),
            'price.gt' => __('Initial price should be greater than 0'),
        ];
    }
}
