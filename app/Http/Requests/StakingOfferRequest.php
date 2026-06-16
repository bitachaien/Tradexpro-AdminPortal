<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StakingOfferRequest extends FormRequest
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
            'coin_type' => 'required|exists:coins,coin_type',
            'period' => 'required|numeric',
            'offer_percentage' => 'required|numeric',
            'minimum_investment' => 'required|numeric|lte:maximum_investment',
            'maximum_investment' => 'required|numeric|gte:minimum_investment',
            'terms_type' => 'required|in:' . STAKING_TERMS_TYPE_STRICT . ',' . STAKING_TERMS_TYPE_FLEXIBLE,
            'phone_verification' => 'required|in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE,
            'kyc_verification' => 'required|in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE,
            'status' => 'required|in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE,
            'body' => 'required|string',
        ];

        if (isset($this->user_minimum_holding_amount)) {
            $rules['user_minimum_holding_amount'] = 'numeric|:0';
        }

        if (isset($this->minimum_maturity_period)) {
            $rules['minimum_maturity_period'] = 'numeric|lte:period';
        }

        if (isset($this->terms_type) && $this->terms_type == STAKING_TERMS_TYPE_FLEXIBLE) {
            $rules['minimum_maturity_period'] = 'required|lte:period';
        }

        if (isset($this->registration_before)) {
            $rules['registration_before'] = 'numeric|min:0';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'coin_type.required' => __('Please, Select a Coin type'),
            'coin_type.exists' => __('Invalid Coin type'),
            'period.required' => __('Enter Period'),
            'period.numeric' => __('Period must be number'),
            'offer_percentage.required' => __('Enter Offer Percentage amount'),
            'offer_percentage.numeric' => __('Percentage must be number'),
            'minimum_investment.required' => __('Enter Minimum Investment amount'),
            'minimum_investment.numeric' => __('Minimum Investment must be number'),
            'maximum_investment.required' => __('Enter Maximum Investment amount'),
            'maximum_investment.numeric' => __('Maximum Investment must be number'),
            'terms_type.numeric' => __('Please, Select Terms Type'),
            'terms_type.in' => __('Please, Select Terms Type'),
            'minimum_maturity_period.required' => __('Enter Minimum Maturity Period'),
            'registration_before.required' => __('User Registration Before is required'),
            'registration_before.numeric' => __('User Registration Before must be number'),
            'phone_verification.required' => __('Phone verification status is required'),
            'phone_verification.in' => __('Phone verification status is invalid'),
            'kyc_verification.required' => __('KYC verification status is required'),
            'kyc_verification.in' => __('KYC verification status is invalid'),
            'status.required' => __('Status is required'),
            'status.in' => __('Status is invalid'),
            'body.required' => __('Terms and condition is required'),
            'body.string' => __('Terms and condition must be string'),
            'user_minimum_holding_amount.numeric' => __('User minimum holding amount must be number'),
            'user_minimum_holding_amount.min' => __('User minimum holding amount must be greater than zero')
        ];
    }
}
