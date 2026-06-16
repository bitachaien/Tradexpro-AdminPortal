<?php

namespace App\Http\Services;

use App\Exceptions\InvalidRequestException;
use App\Http\Repositories\CoinSettingRepository;
use App\Model\Coin;
use App\Model\CoinSetting;

class CoinSettingService extends BaseService
{

    public $model = CoinSetting::class;
    public $repository = CoinSettingRepository::class;
    public $coinService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->coinService = new CoinService();
    }

    // get coin setting
    public function getCoinSettings($coinId)
    {
        $coin = $this->coinService->getCoinDetailsById($coinId);
        if (empty($coin))
            throw new InvalidRequestException(__('Coin not found'));

        $coinSetting = $this->object->getCoinSettingData($coinId, $coin->network);
        if (empty($coinSetting))
            throw new InvalidRequestException(__('Coin settings not found'));

        return $coinSetting;
    }

    // update coin setting
    public function updateCoinSetting($request)
    {
        $response = responseData(false);
        try {
            $coin = $this->coinService->getCoinDetailsById(decrypt($request->coin_id));
            if (empty($coin)) {
                return responseData(false, __('Coin not found'));
            }
            $data = $this->object->getCoinSettingData($coin->id, $coin->network);

            if ($data->network == BITGO_API) {
                $response = $this->updateBitgoApi($data->coin_id, $request);
            } elseif ($data->network == BITCOIN_API) {
                $response = $this->updateBitCoinApi($data->coin_id, $request);
            } else {
                $response = $this->updateERCCoinApi($data->coin_id, $request);
            }
        } catch (\Exception $e) {
            storeException('updateCoinSetting', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    // update bitcoin api
    public function updateBitCoinApi($coinId, $request)
    {
        try {
            $data = [
                'coin_api_user' => $request->coin_api_user,
                'coin_api_pass' => encrypt($request->coin_api_pass),
                'coin_api_host' => $request->coin_api_host,
                'coin_api_port' => $request->coin_api_port,
                'check_encrypt' => STATUS_SUCCESS,
            ];
            $this->object->updateWhere(['coin_id' => $coinId], $data);
            $response = responseData(true, __('Coin api setting updated successfully'));
        } catch (\Exception $e) {
            storeException('updateBitCoinApi', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    // update bitcoin api
    public function updateBitgoApi($coinId, $request)
    {
        try {
            $data = [
                'bitgo_wallet_id' => $request->bitgo_wallet_id,
                'bitgo_wallet' => encrypt($request->bitgo_wallet),
                'chain' => $request->chain,
                'check_encrypt' => STATUS_SUCCESS,
            ];
            $this->object->updateWhere(['coin_id' => $coinId], $data);
            $response = responseData(true, __('Coin api setting updated successfully'));
        } catch (\Exception $e) {
            storeException('updateBitgoApi', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
    // update erc20 or bep20 api
    public function updateERCCoinApi($coinId, $request)
    {
        try {
            $data = [
                'contract_coin_name' => $request->contract_coin_name,
                'chain_link' => $request->chain_link,
                'chain_id' => $request->chain_id,
                'contract_address' => $request->contract_address,
                'wallet_address' => $request->wallet_address,
                'contract_decimal' => $request->contract_decimal,
                'gas_limit' => $request->gas_limit,
                'check_encrypt' => STATUS_SUCCESS,
            ];

            $coin_update_data = [];
            if (isset($request->last_block_number))
                $coin_update_data['last_block_number'] = $request->last_block_number;

            if (isset($request->from_block_number))
                $coin_update_data['from_block_number'] = $request->from_block_number;

            if (isset($request->to_block_number))
                $coin_update_data['to_block_number'] = $request->to_block_number;

            if ($coin_update_data)
                $coin_update = Coin::where('id', $coinId)->update($coin_update_data);

            $this->object->updateWhere(['coin_id' => $coinId], $data);
            $response = responseData(true, __('Coin api setting updated successfully'));
        } catch (\Exception $e) {
            storeException('updateERCCoinApi', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
    // update coin setting
    public function adjustBitgoWallet($coinId)
    {
        $bitgoApi = new BitgoWalletService();
        $response = responseData(false);
        try {
            $coin = $this->coinService->getCoinDetailsById($coinId);
            if (empty($coin)) {
                return responseData(false, __('Coin not found'));
            }
            $data = $this->object->getCoinSettingData($coin->id, $coin->network);
            if ($data->network == BITGO_API) {
                if (empty($data->bitgo_wallet_id)) {
                    return responseData(false, __('Please add your bitgo wallet id first'));
                }
                $getWallet = $bitgoApi->getBitgoWallet($data->coin_type, $data->bitgo_wallet_id);
                if ($getWallet['success']) {
                    $datas = [
                        'bitgo_deleted_status' => $getWallet['data']['deleted'],
                        'bitgo_approvalsRequired' => $getWallet['data']['approvalsRequired'],
                        'bitgo_wallet_type' => $getWallet['data']['type'],
                        'webhook_status' => 1,
                    ];
                    $this->object->updateWhere(['coin_id' => $coinId], $datas);
                    $response = responseData(true, __('Bitgo wallet adjusted successfully'));
                } else {
                    $response = responseData(false, $getWallet['message']);
                }
            } else {
                $response = responseData(false, __('This coin API is not a bitgo wallet api'));
            }
        } catch (\Exception $e) {
            storeException('updateCoinSetting', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
}
