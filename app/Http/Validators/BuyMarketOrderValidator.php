<?php

namespace App\Http\Validators;

use App\Http\Validators\Traits\SpotOrderValidatorTrait;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Services\CoinService;
use Illuminate\Support\Facades\Auth;

class BuyMarketOrderValidator extends FormRequest
{
    use SpotOrderValidatorTrait;

    private $coinType;

    public function __construct(private CoinService $coinService)
    {
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        $coinSetting = $this->getCoinSetting(
            $this->coinService,
            $this->trade_coin_id
        );

        $minimumBuyAmount = $this->getMinimumBuyAmount($coinSetting);
        $this->coinType = $this->getCoinType($coinSetting);

        return [
            'amount' => "required|numeric|between:$minimumBuyAmount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(), 'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'amount.required' => __('Amount field can not be Empty'),
            'amount.numeric' => __('Amount Field Must be Numeric Value'),
            'amount.between' => __('Minimum Buy amount of :ctype should be :min!', ['ctype' => $this->coinType]),
            'trade_coin_id.required' => __('Trade coin field is required.'),
            'trade_coin_id.in' => __('Invalid value for trade coin.'),
            'base_coin_id.required' => __('Base coin field is required.'),
            'base_coin_id.in' => __('Invalid value for base coin.'),
            'category.required' => __('Category is required.')
        ];
        if ($this->amount > 99999999999) {
            $message['amount.between'] = __('Maximum Buy amount of :ctype should be 99999999999!', ['ctype' => $this->coinType]);
        }
        return $message;
    }
}
