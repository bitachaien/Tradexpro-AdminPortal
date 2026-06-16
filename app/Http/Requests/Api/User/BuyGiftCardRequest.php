<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class BuyGiftCardRequest extends FormRequest
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
        return [
            'bulk'        => 'required|in:0,1',
            'coin_type'   => 'required|exists:coins',
            'wallet_type' => 'required|in:1,2',
            'uid'         => 'exists:gift_cards',
            'amount'      => 'required|numeric|gt:0',
            'status'      => 'in:0,1',
            'banner_id'   => 'required|exists:gift_card_banners,uid',
            'lock'        => 'required|in:0,1',
            'quantity'    => 'required_if:bulk,1|numeric|gte:1'
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'bulk.required'        => __("Gift card buy request type is required"),
            'bulk.in'              => __("Gift card buy request type is invalid"),

            'coin_type.required'   => __("Coin is requird"),
            'coin_type.exists'     => __("Coin is invalid"),

            'wallet_type.required' => __("Wallet type is required"),
            'wallet_type.in'       => __("Wallet is invalid"),

            'uid.exists'           => __("Gift card is invalid"),

            'amount.required'      => __("Amount is required"),
            'amount.numeric'       => __("Amount is not a number"),
            'amount.gt'            => __("Amount must be greater than zero"),

            // 'status.required'      => __("Status is required"),
            'status.in'            => __("Status is invalid"),

            'banner_id.required'   => __("Banner is required"),
            'banner_id.exists'     => __("Banner is invalid"),

            'lock.required'        => __("Lock is required"),
            'lock.in'              => __("Lock is invalid"),

            'quantity.required_if' => __("Quantity is required"),
            'quantity.numeric'     => __("Quantity must be a number"),
            'quantity.gte'         => __("Quantity must be greater than or equal to 1"),
        ];
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
            $json = ['success'=>false,
                'data'=>[],
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
