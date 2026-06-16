<?php

namespace App\Http\Validators;

use App\Http\Validators\Traits\SpotOrderValidatorTrait;
use App\Http\Services\CoinService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class MarketSellOrderValidator extends FormRequest
{
    use SpotOrderValidatorTrait;
    
    private $coinType;

    public function __construct(private CoinService $coinService) {}

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

        $minimumSellAmount = $this->getMinimumSellAmount($coinSetting);
        $this->coinType = $this->getCoinType($coinSetting);

        return [
            'amount' => "required|numeric|between:$minimumSellAmount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array_column(coin_type_restrict_trade(), 'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'amount.required' => __('Amount field can not be Empty'),
            'amount.numeric' => __('Amount Field Must be Numeric Value'),
            'amount.between' => __('Minimum Sell amount of :ctype should be :min!', ['ctype' => $this->coinType]),
            'trade_coin_id.required' => __('Trade coin field is required.'),
            'base_coin_id.required' => __('Base coin field is required.'),
            'category.required' => __('Category is required.'),
        ];

        if ($this->amount > 99999999999) {
            $message['amount.between'] = __('Maximum Sell amount of :ctype should be 99999999999!', ['ctype' => $this->coinType]);
        }

        return $message;
    }

}
