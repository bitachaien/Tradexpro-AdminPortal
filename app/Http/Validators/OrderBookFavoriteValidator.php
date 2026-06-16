<?php
/**
 * Created by Masum.
 * User: itech
 * Date: 11/15/18
 * Time: 4:27 PM
 */


namespace App\Http\Validators;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderBookFavoriteValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public $coin_type;

    public function authorize()
    {
        return Auth::user()->status == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'price' => 'required',
            'type' => 'required',
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(),'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'trade_coin_id.required' => __('Coin type field is required.'),
            'base_coin_id.required' => __('Base coin field is required.'),
            'price.required' => __('Price field can not be Empty'),
            'price.numeric' => __('Invalid value for Sell order price!'),
        ];

        return $message;
    }

}
