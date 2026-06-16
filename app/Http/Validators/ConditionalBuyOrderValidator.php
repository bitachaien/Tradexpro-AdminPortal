<?php
/**
 * Created by Masum.
 * User: itech
 * Date: 11/15/18
 * Time: 4:27 PM
 */


namespace App\Http\Validators;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConditionalBuyOrderValidator extends FormRequest
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
        $coinSetting = getService(['method' => 'getCoin', 'params' => ['id' => $this->trade_coin_id]]);
        $minimum_buy_amount = 0;
        if (!empty($coinSetting)) {
            $this->coin_type = $coinSetting[0]['coin_type'];
            $minimum_buy_amount = $coinSetting[0]['minimum_buy_amount'];
        }
        return [
            'buy_price' => 'required|numeric|between:0.00000001,99999999999.99999999|',
            'buy_amount' => "required|numeric|between:$minimum_buy_amount,99999999999.99999999",

            'sell_price_1' => 'required_with:sell_amount_1|nullable|numeric|between:0.00000001,99999999999.99999999',
            'sell_amount_1' => 'required_with:sell_price_1|nullable|numeric|between:0.00000001,99999999999.99999999',

            'sell_price_2' => 'required_with:sell_amount_2|nullable|numeric|between:0.00000001,99999999999.99999999',
            'sell_amount_2' => 'required_with:sell_price_2|nullable|numeric|between:0.00000001,99999999999.99999999',

            'sell_price_3' => 'required_with:sell_amount_3|nullable|numeric|between:0.00000001,99999999999.99999999',
            'sell_amount_3' => 'required_with:sell_price_3|nullable|numeric|between:0.00000001,99999999999.99999999',

            'stop_price' => 'required_with:stop_limit|nullable|numeric|between:0.00000001,99999999999.99999999',
            'stop_limit' => 'required_with:stop_price|nullable|numeric|between:0.00000001,99999999999.99999999',

            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(),'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),

        ];
    }

    public function messages()
    {
        $message = [
            'buy_price.required' => __('Advance Buy price is required!'),
            'buy_price.between' => __('Invalid value for Advance Buy price!'),
            'buy_price.numeric' => __('Invalid value for Advance Buy price!'),
            'buy_amount.required' => __('Advance Buy amount is required!'),
            'buy_amount.numeric' => __('Invalid value for Advance Buy amount!'),
            'buy_amount.between' => __('Minimum Advance Buy amount of :ctype should be :min!', ['ctype' => $this->coin_type]),

            'sell_price_1.between' => __('Invalid value for Advanced Sell Price1!'),
            'sell_price_1.numeric' => __('Invalid value for Advanced Sell Price1!'),
            'sell_price_1.required_with' => __('Sell price1 field is required as sell amount1 field is not empty.'),

            'sell_amount_1.required_with' => __('Sell amount1 field is required as sell price1 field is not empty.'),
            'sell_amount_1.numeric' => __('Invalid value for Advanced Sell Amount1!'),
            'sell_amount_1.between' => __('Invalid value for Advanced Sell Amount1!'),


            'sell_price_2.between' => __('Invalid value for Advanced Sell Price2!'),
            'sell_price_2.numeric' => __('Invalid value for Advanced Sell Price2!'),
            'sell_price_2.required_with' => __('Sell price2 field is required as sell amount2 field is not empty.'),

            'sell_amount_2.required_with' => __('Sell amount2 field is required as sell price2 field is not empty.'),
            'sell_amount_2.numeric' => __('Invalid value for Advanced Sell Amount2!'),
            'sell_amount_2.between' => __('Invalid value for Advanced Sell Amount2!'),


            'sell_price_3.between' => __('Invalid value for Advanced Sell Price3!'),
            'sell_price_3.numeric' => __('Invalid value for Advanced Sell Price3!'),
            'sell_price_3.required_with' => __('Sell price3 field is required as sell amount3 field is not empty.'),

            'sell_amount_3.required_with' => __('Sell amount3 field is required as sell price3 field is not empty.'),
            'sell_amount_3.numeric' => __('Invalid value for Advanced Sell Amount3!'),
            'sell_amount_3.between' => __('Invalid value for Advanced Sell Amount3!'),


            'stop_price.numeric' => __('Invalid value for Advanced Stop!'),
            'stop_price.between' => __('Invalid value for Advanced Stop!'),

            'stop_limit.numeric' => __('Invalid value for Advanced limit!'),
            'stop_limit.between' => __('Invalid value for Advanced limit!'),

            'trade_coin_id.required' => __('Trade coin type is required!'),
            'base_coin_id.required' => __('Base coin field is required.')
        ];

        if ($this->abamount > 99999999999) {
            $message['buy_amount.between'] = __('Maximum Advance Buy amount of :ctype should be 99999999999!', ['ctype' => $this->ctype]);
        }
        return $message;
    }
}
