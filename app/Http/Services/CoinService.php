<?php

namespace App\Http\Services;

use App\Exceptions\InvalidRequestException;
use App\User;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\CoinSetting;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Hash;
use Modules\IcoLaunchpad\Entities\IcoToken;
use App\Http\Repositories\AdminCoinRepository;
use App\Model\CurrencyList;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

class CoinService extends BaseService
{

    public $model = Coin::class;
    public $repository = AdminCoinRepository::class;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
    }

    public function getCoinTypeById(int $id): ?string
    {
        return $this->object->getCoinTypeById($id);
    }

    public function getCoin($data)
    {
        $object = $this->object->getDocs($data);

        if (empty($object)) {
            return null;
        }

        return $object;
    }

    public function getCoinListActive()
    {
        try {
            $data = $this->object->getCoinListActive();
            $response = ['success' => true, 'message' => __('Active Coin list!'), 'data' => $data];
        } catch (\Exception $e) {
            storeException("getCoinListActive", $e->getMessage());
            $response = ['success' => true, 'message' => __('Something went wrong!')];
        }
        return $response;
    }

    public function getPrimaryCoin()
    {
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $this->object->getPrimaryCoin();

        return $object;
    }

    public function getBuyableCoin()
    {
        $object = $this->object->getBuyableCoin();
        if (empty($object)) {
            return null;
        }

        return json_encode($object);
    }

    public function getBuyableCoinDetails($coinId)
    {
        $object = $this->object->getBuyableCoinDetails($coinId);
        if (empty($object)) {
            return null;
        }
        return json_encode($object);
    }

    public function generate_address($coinId)
    {
        $address = '';

        $coinApiCredential = $this->object->getCoinApiCredential($coinId);
        if (isset($coinApiCredential)) {
            $api = new BitCoinApiService($coinApiCredential->user, decryptId($coinApiCredential->password), $coinApiCredential->host, $coinApiCredential->port);
            $address = $api->getNewAddress();
        }

        return json_encode($address);
    }

    public function getCoinApiCredential($coinId)
    {
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $coinRepo->getCoinApiCredential($coinId);
        if (empty($object)) {
            return null;
        }
        return $object;
    }

    public function addNewCoin($request): array
    {
        $data = [
            'currency_type' => $request->currency_type,
            'name' => $request->name,
            'coin_type' => strtoupper($request->coin_type),
            'network' => $request->network,
        ];
        if ($request->currency_type == CURRENCY_TYPE_FIAT) {
            if ($currency = CurrencyList::whereCode($request->coin_type)->first()) {
                $data['currency_id'] = $currency->id;
                $data['coin_price'] = bcdiv(1, $currency->rate, 8);
            }
        } else {
            if ($request->get_price_api == 1) {
                $pair = strtoupper($request->coin_type) . '_' . 'USDT';

                $apiData = getPriceFromApi($pair);
                if (@$apiData['success'] == false)
                    throw new InvalidRequestException(__('Get api data failed, please add manual price'));

                $data['coin_price'] = $apiData['data']['price'];
            } else {
                $data['coin_price'] = $request->coin_price;
            }
        }
        $coin = $this->object->addCoin($data); // Add new coin to the database

        // TODO: Wallet will bulk create with queue job
        // Wallet::where(['coin_id' => $coinData->id])->update(['coin_type' => $data['coin_type'], 'name' => $data['coin_type'] . ' wallet']);

        return $this->responseData(true, __('New coin added successfully'), $coin);
    }

    public function updateCoin($request, int $coin_id)
    {
        $coinData = Coin::find($coin_id);
        if (empty($coinData))
            throw new InvalidRequestException(__('Coin not found'));

        $data = [
            'network' => $request->network,
            'name' => $request->name,
            'coin_price' => $request->coin_price,
            'minimum_buy_amount' => $request->minimum_buy_amount,
            'minimum_sell_amount' => $request->minimum_sell_amount,
            'minimum_withdrawal' => $request->minimum_withdrawal,
            'maximum_withdrawal' => $request->maximum_withdrawal,
            'withdrawal_fees' => $request->withdrawal_fees,
            'max_send_limit' => $request->max_send_limit ?? 0,
            'withdrawal_fees_type' => $request->withdrawal_fees_type ?? 2,
            'admin_approval' => $request->admin_approval ?? 2,
            'is_deposit' => $request->is_deposit ? 1 : 0,
            'is_withdrawal' => $request->is_withdrawal ? 1 : 0,
            'status' => $request->status ? 1 : 0,
            'trade_status' => $request->trade_status ? 1 : 0,
            'is_wallet' => $request->is_wallet ? 1 : 0,
            'is_buy' => $request->is_buy ? 1 : 0,
            'is_virtual_amount' => $request->is_virtual_amount ? 1 : 0,
            'is_currency' => $request->is_currency ? 1 : 0,
            'is_transferable' => $request->is_transferable ? 1 : 0,
            'is_demo_trade' => $request->is_demo_trade ? 1 : 0
        ];
        if (!empty($request->coin_icon)) {
            $icon = uploadFile($request->coin_icon, IMG_ICON_PATH, '');
            if ($icon != false) {
                $data['coin_icon'] = $icon;
            }
        }
        $this->object->updateCoin($coin_id, $data); // Update coin info

        if ($coinData->network != $data['network']) {
            // TODO: Have to check the function and insert network for the existing customer 
            // $checkNetwork = checkCoinNetworkUpdateCondition($coinData);
            $result['updateNetwork'] = true; // For redirect the coin setting page
        }
        return $this->responseData(true, __('Coin updated successfully'), $result);
    }

    public function getCoinDetailsById($coinId)
    {
        return $this->object->getCoinDetailsById($coinId);
    }

    // admin coin delete
    public function adminCoinDeleteProcess($coinId)
    {
        $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        DB::beginTransaction();
        try {
            $coin = Coin::find($coinId);
            if ($coin) {
                if ($coin->coin_type == 'BTC' || $coin->coin_type == 'USDT') {
                    return ['success' => false, 'message' => __('You never delete this coin, because this is on of the base coin '), 'data' => []];
                }
                $check = checkCoinDeleteCondition($coin);
                if ($check['success'] == true) {
                    $coin->delete();
                    Wallet::where(['coin_id' => $coin->id])->delete();
                    $response = ['success' => true, 'message' => __('Coin deleted successfully'), 'data' => []];
                } else {
                    $response = ['success' => false, 'message' => $check['message'], 'data' => []];
                }
            } else {
                $response = ['success' => false, 'message' => __('Coin not found'), 'data' => []];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminCoinDeleteProcess', $e->getMessage());
        }
        DB::commit();
        return $response;
    }

    public function deleteWebhook($service, $coin, $request)
    {
        return $service->removeWalletWebhook($coin->coin_type, $coin->bitgo_wallet_id, $request->type, $coin->bitgo_webhook_url, $coin->bitgo_webhook_id);
    }

    // add webhook
    public function webhookSaveProcess($request)
    {
        try {

            $coin = Coin::join('coin_settings', 'coins.id', '=', 'coin_settings.coin_id')->where(['coin_settings.coin_id' => decrypt($request->coin_id)])->first();
            if (isset($coin)) {
                if (empty($coin->bitgo_wallet_id)) {
                    $response = [
                        'success' => false,
                        'message' => __("Your Bitgo wallet id not set yet !!"),
                        'data' => ""
                    ];
                    return $response;
                }
                $bitgoApi = new BitgoWalletService();
                if (($request->url !== $coin->bitgo_webhook_url || $request->numConfirmations !== $coin->bitgo_webhook_numConfirmations) && !empty($coin->bitgo_webhook_url)) {
                    $response = $this->deleteWebhook($bitgoApi, $coin, $request);
                    if (!$response["success"]) {
                        return $response;
                    }
                }
                $allToken = $request->allToken == 1 ? true : false;
                $bitgoResponse = $bitgoApi->addWebhook($coin->coin_type, $coin->bitgo_wallet_id, $request->type, $allToken, $request->url, $request->label, intval($request->numConfirmations));
                if (!$bitgoResponse['success']) {
                    storeException('Bitgo webhookSaveProcess', $bitgoResponse['message']);
                    $response = [
                        'success' => false,
                        'message' => $bitgoResponse['message'],
                        'data' => ""
                    ];
                    return $response;
                }
                $coin = CoinSetting::where(['coin_settings.coin_id' => decrypt($request->coin_id)])->update([
                    'bitgo_webhook_label' => $request->label,
                    'bitgo_webhook_type' => $request->type,
                    'bitgo_webhook_url' => $request->url,
                    'bitgo_webhook_numConfirmations' => $request->numConfirmations,
                    'bitgo_webhook_allToken' => $request->allToken,
                    'bitgo_webhook_id' => $bitgoResponse['data']["id"],
                    'webhook_status' => STATUS_ACTIVE
                ]);
                $response = [
                    'success' => true,
                    'message' => __('Webhook updated successful'),
                    'data' => $bitgoResponse
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Coin not found'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('webhookSaveProcess: ', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ""
            ];
        }
        return $response;
    }

    public function saveCoinByICO($ico_id, $data)
    {
        try {
            $response = $this->object->saveCoinByICO($ico_id, $data);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('saveCoinByICO', $e->getMessage());
        }
        return $response;
    }

    public function makeTokenListedToCoin($coin_id)
    {
        $check_module = Module::allEnabled();

        if (!empty($check_module) && (isset($check_module['IcoLaunchpad']) && $check_module['IcoLaunchpad'] == 'IcoLaunchpad')) {
            $coin_details = Coin::find($coin_id);

            if (isset($coin_details)) {
                $token_details = IcoToken::find($coin_details->ico_id);
                if (isset($token_details)) {
                    $pending_token_buy_history_list = TokenBuyHistory::where('token_id', $token_details->id)
                        ->where('status', STATUS_PENDING)->get();

                    if ($pending_token_buy_history_list->count() > 0) {
                        return responseData(false, __('Please, Accept or Reject the pending token buy history, and then try again!'));
                    } else {
                        $ico_phase_list = IcoPhaseInfo::where('ico_token_id', $token_details->id)->get();

                        if ($ico_phase_list->count() == 0) {
                            return responseData(false, __('You can not make this coin listed because this token has no phase!'));
                        }
                        IcoPhaseInfo::where('ico_token_id', $token_details->id)->where('status', STATUS_ACTIVE)->update(['status' => STATUS_INACTIVE]);

                        $coin_details->is_listed = STATUS_ACTIVE;
                        $coin_details->is_withdrawal = STATUS_ACTIVE;
                        $coin_details->is_deposit = STATUS_ACTIVE;
                        $coin_details->is_buy = STATUS_ACTIVE;
                        $coin_details->is_sell = STATUS_ACTIVE;
                        $coin_details->is_listed = STATUS_ACTIVE;
                        $coin_details->trade_status = STATUS_ACTIVE;
                        $coin_details->save();
                        return responseData(true, __('Your ICO Token is listed Successfully!'));
                    }
                }
                return responseData(false, __('ICO Token not found!'));
            } else {
                return responseData(false, __('Invalid Request!'));
            }
        } else {

            return responseData(false, __('Your ICO module is not enabled!'));
        }
    }

    public function getAllActiveCoinList()
    {
        $coin_list = Coin::where('status', '<>', STATUS_DELETED)
            ->where('ico_id', '=', 0)
            ->orWhere('is_listed', STATUS_ACTIVE)->orderBy('id', 'asc')->get();

        $response = ['success' => true, 'message' => __('Active Coin List'), 'data' => $coin_list];

        return $response;
    }

    public function updateWalletKey($request)
    {
        $id = decrypt($request->id);
        $user = auth()->user();

        if (checkGoogleAuth()) {
            if (!isset($request->code)) {
                return ['success' => false, 'message' => __('Google authenticator code is missing!')];
            }

            if (blank($user->google2fa_secret ?? null)) {
                return ['success' => false, 'message' => __('Google authenticator not setup')];
            }

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
            if (!$valid) {
                return [
                    'success' => false,
                    'message' => __('Google authentication code is invalid'),
                ];
            }
        }

        if (!Hash::check($request->password, $user->password))
            return responseData(false, __('Invalid Password!'));

        $coinSettingDetails = CoinSetting::find($id);
        if (isset($coinSettingDetails)) {
            $coinSettingDetails->wallet_key = encrypt($request->wallet_key);
            $coinSettingDetails->save();

            $response = responseData(true, 'Wallet Key is updated successfully!');
        } else {
            $response = responseData(false, __('Coin Settings not found!'));
        }

        return $response;
    }

    public function viewWalletKey($request)
    {
        $user = auth()->user();

        if (checkGoogleAuth()) {
            if (!isset($request->google_authenticator)) {
                return ['success' => false, 'message' => __('Google authenticator code is missing!')];
            }

            if (blank($user->google2fa_secret ?? null)) {
                return ['success' => false, 'message' => __('Google authenticator not setup')];
            }

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->google_authenticator);
            if (!$valid) {
                return [
                    'success' => false,
                    'message' => __('Google authentication code is invalid'),
                ];
            }
        }

        if (!isset($request->id)) {
            return responseData(false, __('Invalid Request!'));
        }

        if (!isset($request->password)) {
            return responseData(false, __('Enter Your Password!'));
        }

        $user_details = User::find($user->id);

        if (isset($user_details)) {
            if (Hash::check($request->password, $user_details->password)) {
                $coinSettingDetails = CoinSetting::find(decrypt($request->id));
                if (isset($coinSettingDetails) && isset($coinSettingDetails->wallet_key)) {
                    return responseData(true, __('Wallet Key details'), decrypt($coinSettingDetails->wallet_key));
                } else {
                    return responseData(false, __('Wallet key not found!'));
                }
            } else {
                return responseData(false, __('Invalid Password!'));
            }
        } else {
            return responseData(false, __('User Details not found!'));
        }
    }
}
