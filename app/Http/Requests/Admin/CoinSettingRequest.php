<?php

namespace App\Http\Requests\Admin;

use App\Model\Coin;
use Illuminate\Foundation\Http\FormRequest;

class CoinSettingRequest extends FormRequest
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
            'coin_id' => 'required'
        ];
        if(isset($this->coin_id)) {
            $coin = Coin::find(decrypt($this->coin_id));
            if($coin) {
                if ($coin->network == BITGO_API) {
                    $rules['bitgo_wallet_id'] = 'required|max:255';
                    $rules['bitgo_wallet'] = 'required|max:255';
                    $rules['chain'] = 'required|integer';
                } elseif($coin->network == BITCOIN_API) {
                    $rules['coin_api_user'] = 'required|max:255';
                    $rules['coin_api_pass'] = 'required|max:255';
                    $rules['coin_api_host'] = 'required|max:255';
                    $rules['coin_api_port'] = 'required|max:255';
                }
            }
        }

        return $rules;
    }
}
