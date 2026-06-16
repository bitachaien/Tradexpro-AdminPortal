<?php

namespace App\Http\Validators;

use App\Http\Validators\Traits\FailedValidatorTrait;
use Illuminate\Foundation\Http\FormRequest;

class TolerenceValidator extends FormRequest
{
    use FailedValidatorTrait;

    public function rules()
    {
        return [
            'base_coin_id' => 'required|int',
            'trade_coin_id' => 'required|int',
        ];
    }

    public function messages()
    {
        $message = [
            'base_coin_id.required' => __('Base coin can not be Empty'),
            'trade_coin_id.required' => __('Trade coin can not be Empty'),
        ];

        return $message;
    }
}
