<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeFeeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()?->role === 1; ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'maker_fee' => 'required|numeric',
            'taker_fee' => 'required|numeric',
        ];
    }
}
