<?php

/**
 * Created by PhpStorm.
 * User: bacchu
 * Date: 9/12/19
 * Time: 12:56 PM
 */

namespace App\Http\Services;

use App\Jobs\UpdateCoinRateUsd;
use App\Model\AffiliationCode;
use App\Model\Buy;
use App\Model\Coin;
use App\Model\CurrencyList;
use App\Model\FiatWithdrawalCurrency;
use App\Model\Sell;
use App\Model\UserVerificationCode;
use App\Model\Wallet;
use App\Repository\AffiliateRepository;
use App\Repository\MarketRepository;
use App\Repository\OfferRepository;
use App\Services\Logger;
use App\Services\MailService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Exception;

class CurrencyService
{

    public $response;
    function __construct() {}

    /**
     * @param $request
     * @return array
     */
    // marketplace data
    public function currencyList()
    {
        return CurrencyList::orderBy('id', 'desc')->get();
    }

    public function getActiveCurrencyList()
    {
        return CurrencyList::where('status', STATUS_ACTIVE)->orderBy('id', 'desc')->get();
    }

    public function currencyAddEdit($request, $auto = false)
    {
        DB::beginTransaction();
        try {
            $response = isset($request->id) ? __("Currency updated ") : __("Currency created ");
            $id = $request->id ?? 0;
            $status =  isset($request->status) ? true : false;
            $check = $auto ? ['code' => $request->code] : ['id' => $id];
            CurrencyList::updateOrCreate($check, [
                'name' => $request->name,
                'code' => $request->code,
                'symbol' => $request->symbol,
                'rate' => $request->rate,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            storeException($e, "Currency Add Edit", $e->getMessage());
            return ["success" => false, "message" => $response . __("failed")];
        }
        DB::commit();
        return ["success" => true, "message" => $response . __("successfully")];
    }

    public function saveAllCurrency()
    {
        $currency = fiat_currency_array();
        foreach ($currency as $item) {
            if (!isset($item['rate']))
                $item['rate'] = 1;
            $item['status'] = 1;
            $respose = $this->currencyAddEdit((object)$item, true);
        }
        $responseCurrencyExchangeRate = $this->getCurrencyRateData();

        if ($responseCurrencyExchangeRate['success']) {
            $rates = $responseCurrencyExchangeRate['data'];
            if ($rates['rates']) {
                foreach ($rates['rates'] as $type => $rate) {
                    foreach ($currency as $index => $item) {
                        if ($item['code'] == $type)
                            $currency[$index]['rate'] = $rate;
                    }
                }
            }
            foreach ($currency as $item) {
                if (!isset($item['rate']))
                    $item['rate'] = 1;
                $item['status'] = 1;
                $respose = $this->currencyAddEdit((object)$item, true);
            }
        }
    }

    public function currencyStatusUpdate($id)
    {
        DB::beginTransaction();
        try {
            if ($c = CurrencyList::find($id)) {
                if (Coin::whereCoinType($c->code)->first()) {
                    return responseData(false, __("This currency has been listed as coin. Please remove the coin to disable the currency"));
                }
                $status = !$c->status;
                if ($c->update(['status' => $status])) {
                    DB::commit();
                    return responseData(true, __("Status updated successfully"));
                }
                return responseData(false, __("Status failed to update"));
            }
            return responseData(false, __("Currency not found"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("Currency Status Changed", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function currencyRateSave()
    {
        $responseCurrencyExchangeRate = $this->getCurrencyRateData();
        DB::beginTransaction();
        try {

            if ($responseCurrencyExchangeRate['success']) {
                $data = $responseCurrencyExchangeRate['data'];
                if ($data['rates']) {
                    foreach ($data['rates'] as $type => $rate) {
                        $usd = (is_numeric($rate) && $rate > 0) ? bcdivx(1, number_format($rate, 8, ".", ""), 4) : $rate;
                        if ($coin = Coin::where("coin_type", $type)->first()) {
                            $coin->update(['coin_price' => $usd]);
                        }
                        CurrencyList::where('code', $type)->update(['rate' => $rate ? $rate : 1]);
                    }
                }
            } else {
                return $responseCurrencyExchangeRate;
            }
        } catch (\Exception $e) {
            storeException('currencyRateSave', $e->getMessage());
            DB::rollBack();
            $this->response = ['success' => false, 'message' => __('Currency Rate Update failed')];
        }
        DB::commit();
        $this->response = ['success' => true, 'message' => __('Currency Rate Update')];
    }
    public function getCurrencyRateData()
    {
        $apiKey = allsetting('CURRENCY_EXCHANGE_RATE_API_KEY') ?? null;
        if ($apiKey) {
            $headers = ['Content-Type: application/json'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://openexchangerates.org/api/latest.json?app_id=' . $apiKey);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($result, true);
            if (isset($responseData['error'])) {
                return responseData(false, $responseData['message'], $responseData);
            } else {
                return responseData(true, __('Currency exchange rate list'), $responseData);
            }
        } else {
            return responseData(false, __('API key is not set!'));
        }
    }

    public function updateCoinRate()
    {
        try {
            $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
            if (isset($coins[0])) {
                dispatch(new UpdateCoinRateUsd($coins));
            }
        } catch (\Exception $e) {
            storeException("Update Coin Rate", $e->getMessage());
            return ["success" => false, "message" => __("Coins rate updated Failed")];
        }
        return ["success" => true, "message" => __("Coins rate update process started successfully, It will take some time")];
    }
    public function updateCoinRateCorn()
    {
        try {
            $coins = Coin::where(['currency_type' => CURRENCY_TYPE_CRYPTO, 'status' => STATUS_ACTIVE])->get();
            if (isset($coins[0])) {
                foreach ($coins as $coin) {
                    $pair = explode('.', $coin->coin_type)[0];
                    if ($pair == 'USDT') continue;
                    $pair = $pair . '_' . 'USDT';
                    $res = getPriceFromApi($pair);
                    if ($res['success']) {
                        $coin->coin_price = $res['data']['price'];
                        $coin->save();
                    }
                }
            }
            $this->currencyRateSave();
        } catch (\Exception $e) {
            storeBotException("Update Coin Rate", $e->getMessage());
            return ["success" => false, "message" => __("Coins rate updated Failed")];
        }
        return ["success" => true, "message" => __("Coins rate update process started successfully, It will take some time")];
    }


    public function withdrawalCurrencyStatusUpdate($id)
    {
        DB::beginTransaction();
        try {
            $c = FiatWithdrawalCurrency::find($id);
            $status = !$c->status;
            $c->update(['status' => $status]);
        } catch (\Exception $e) {
            DB::rollBack();
            storeException($e, "withdrawal Currency Status Changed", $e->getMessage());
            return false;
        }
        DB::commit();
        return true;
    }

    public function withdrawalCurrencySaveProcess($request)
    {
        $response = responseData(false);
        try {
            if ($request->currency_id) {
                $exist = FiatWithdrawalCurrency::where(['currency_id' => $request->currency_id])->first();
                if ($exist) {
                    $response = responseData(false, __('Currency already added'));
                } else {
                    FiatWithdrawalCurrency::firstOrCreate(['currency_id' => $request->currency_id], ['status' => STATUS_ACTIVE]);
                    $response = responseData(false, __('Currency added successfully'));
                }
            } else {
                $response = responseData(false, __('Currency is required'));
            }
        } catch (\Exception $e) {
            storeException('withdrawalCurrencySaveProcess', $e->getMessage());
        }
        return $response;
    }

    public function withdrawalCurrencyDeleteProcess($id)
    {
        $response = responseData(false);
        try {
            FiatWithdrawalCurrency::findOrFail($id)->delete();
            $response = responseData(true, __('Deleted successfully'));
        } catch (\Exception $e) {
            storeException('withdrawalCurrencyDeleteProcess', $e->getMessage());
        }
        return $response;
    }
}
