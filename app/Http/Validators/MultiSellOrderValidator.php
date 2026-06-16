<?php
/**
 * Created by MAsum.
 * User: itech
 * Date: 11/20/18
 * Time: 12:00 PM
 */

namespace App\Http\Validators;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MultiSellOrderValidator extends FormRequest
{
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
        $minimum_sell_amount = 0;
        if (!empty($coinSetting)) {
            $this->coin_type = $coinSetting[0]['coin_type'];
            $minimum_sell_amount = $coinSetting[0]['minimum_sell_amount'];
        }
        return [
            'price_1' => 'required_with:amount_1|nullable|numeric|between:0.00000001,99999999999.99999999',
            'amount_1' => "required_with:price_1|nullable|numeric|between:$minimum_sell_amount,99999999999.99999999",
            'price_2' => 'required_with:amount_2|nullable|numeric|between:0.00000001,99999999999.99999999',
            'amount_2' => "required_with:price_2|nullable|numeric|between:$minimum_sell_amount,99999999999.99999999",
            'price_3' => 'required_with:amount_3|nullable|numeric|between:0.00000001,99999999999.99999999',
            'amount_3' => "required_with:price_3|nullable|numeric|between:$minimum_sell_amount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(), 'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'price_1.required_with' => __('Sell price1 field is required as sell amount1 field is not empty.'),
            'price_1.between' => __('Invalid value for sell price1!'),
            'price_1.numeric' => __('Invalid value for sell price1!'),
            'amount_1.required_with' => __('Sell amount1 field is required as sell price1 field is not empty.'),
            'amount_1.numeric' => __('Invalid value for sell amount!'),
            'amount_1.between' => __('Minimum Sell amount1 of :ctype should be :min!', ['ctype' => $this->coin_type]),

            'price_2.required_with' => __('Sell price2 field is required as sell amount2 field is not empty.'),
            'price_2.between' => __('Invalid value for sell price2!'),
            'price_2.numeric' => __('Invalid value for sell price2!'),
            'amount_2.required_with' => __('Sell amount2 field is required as sell price2 field is not empty.'),
            'amount_2.numeric' => __('Invalid value for sell amount2!'),
            'amount_2.between' => __('Minimum Sell amount2 of :ctype should be :min!', ['ctype' => $this->coin_type]),

            'price_3.required_with' => __('Sell price3 field is required as sell amount3 field is not empty.'),
            'amount_3.required_with' => __('Sell amount3 field is required as sell price3 field is not empty.'),
            'price_3.between' => __('Invalid value for sell price3!'),
            'price_3.numeric' => __('Invalid value for sell price3!'),
            'amount_3.numeric' => __('Invalid value for sell amount3!'),
            'amount_3.between' => __('Minimum Sell amount3 of :ctype should be :min!', ['ctype' => $this->coin_type]),

            'trade_coin_id.required' => __('Coin type field is required.'),
            'base_coin_id.required' => __('Base coin field is required.')
        ];
        if ($this->amount_1 > 99999999999) {
            $message['amount_1.between'] = __('Maximum Sell amount1 of :ctype should be 99999999999!', ['ctype' => $this->coin_type]);
        }
        if ($this->amount_2 > 99999999999) {
            $message['amount_2.between'] = __('Maximum Sell amount2 of :ctype should be 99999999999!', ['ctype' => $this->coin_type]);
        }
        if ($this->amount_3 > 99999999999) {
            $message['amount_3.between'] = __('Maximum Sell amount3 of :ctype should be 99999999999!', ['ctype' => $this->coin_type]);
        }
        return $message;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = '';
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors = $errors . $error . "\n";
            }
        }
        $json = [
            'status' => false,
            'message' => $errors
        ];

        $response = new JsonResponse($json, 200);

        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
    }
}
