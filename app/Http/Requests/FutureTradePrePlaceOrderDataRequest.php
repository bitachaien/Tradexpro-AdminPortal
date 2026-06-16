<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FutureTradePrePlaceOrderDataRequest extends FormRequest
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
            'margin_mode'=>'required|in:'.MARGIN_MODE_ISOLATED.','.MARGIN_MODE_CROSS,
            'order_type'=>'required|in:'.LIMIT_ORDER.','.MARKET_ORDER.','.STOP_LIMIT_ORDER.','.STOP_MARKET_ORDER,
            'coin_pair_id'=>'required|integer|exists:coin_pairs,id',
            'amount_type'=>'required|in:'.AMOUNT_TYPE_BASE.','.AMOUNT_TYPE_TRADE,
            'amount'=>'required|numeric|gt:0',
            'leverage_amount'=>'required|numeric',
            'trade_type'=>'required|in:'.FUTURE_TRADE_TYPE_OPEN.','.FUTURE_TRADE_TYPE_CLOSE,
        ];

        if($this->order_type == LIMIT_ORDER || $this->order_type == STOP_LIMIT_ORDER)
        {
            $rules['price'] = 'required|numeric|gt:0';
        }

        if($this->order_type == STOP_LIMIT_ORDER || $this->order_type == STOP_MARKET_ORDER)
        {
            $rules['stop_price'] = 'required|numeric|gt:0';
        }

        if(isset($this->take_profit))
        {
            $rules['take_profit'] = 'numeric|gt:0';
        }
        if(isset($this->stop_loss))
        {
            $rules['stop_loss'] = 'numeric|gt:0';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'margin_mode.required' => __('Margin mode is required!'),
            'margin_mode.in' => __('Invalid Input for Margin mode!'),
            'order_type.required' => __('Order Type is required!'),
            'order_type.in' => __('Invalid Input for Order Type!'),
            'amount_type.required' => __('Amount Type is required!'),
            'amount_type.in' => __('Invalid Input for Amount Type!'),
            'coin_pair_id.required' => __('Coin Pair ID is required!'),
            'coin_pair_id.integer' => __('Coin Pair ID must be number!'),
            'coin_pair_id.exists' => __('Coin Pair ID is invalid!'),
            'price.required' => __('Price is required!'),
            'price.numeric' => __('Price is invalid!'),
            'price.gt' => __('Price must be greater than 0!'),
            'stop_price.required' => __('Stop Price is required!'),
            'stop_price.numeric' => __('Stop Price is invalid!'),
            'stop_price.gt' => __('Stop Price must be greater than 0!'),
            'amount.required' => __('Amount is required!'),
            'amount.numeric' => __('Amount is invalid!'),
            'take_profit.required' => __('Take profit is required!'),
            'take_profit.numeric' => __('Take profit is invalid!'),
            'stop_loss.required' => __('Stop Loss is required!'),
            'stop_loss.numeric' => __('Stop Loss is invalid!'),
            'leverage_amount.required' => __('Leverage amount is required!'),
            'leverage_amount.numeric' => __('Leverage amount is invalid!'),
            'trade_type.required' => __('Trade type is required!'),
            'trade_type.in' => __('Invalid Input for Trade type!'),
        ];

        return $messages;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = ['success' => false,
                'data' => [],
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}
