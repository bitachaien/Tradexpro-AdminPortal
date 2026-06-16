<?php
/**
 * Created by Masum.
 * User: itech
 * Date: 11/15/18
 * Time: 4:27 PM
 */


namespace App\Http\Validators;

use App\Http\Services\CoinPairService;
use App\Http\Services\CoinService;
use App\Http\Validators\Traits\SpotOrderValidatorTrait;
use App\Model\CoinPair;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BuyOrderValidator extends FormRequest
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

        $minimumBuyAmount = $this->getMinimumBuyAmount($coinSetting);
        $this->coinType = $this->getCoinType($coinSetting);

        return [
            'price' => 'required|numeric|min:0.00000001',
            'amount' => "required|numeric|between:$minimumBuyAmount,99999999999.99999999",
            'trade_coin_id' => 'required|in:' . arrValueOnly(array: array_column(coin_type_restrict_trade(),'id')),
            'base_coin_id' => 'required|in:' . arrValueOnly(bscointype()),
        ];
    }

    public function messages()
    {
        $message = [
            'price.required' => __('Price field can not be Empty'),
            'price.numeric' => __('Invalid value for buy order price!'),
            'price.between' => __('Invalid value for buy order price!'),
            'is_market.integer' => __('Invalid value for order type.'),
            'is_market.in' => __('Invalid value for order type.'),
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
