<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\CoinSettingRepositoryInterface;
use App\Model\CoinSetting;

class CoinSettingRepository extends CommonRepository implements CoinSettingRepositoryInterface
{

    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getCoinSettingData(int $coinId, int $network)
    {
        $coinSetting = $this->createCoinSetting($coinId,  $network);
        return CoinSetting::join('coins', 'coins.id', '=', 'coin_settings.coin_id')
            ->select('coins.*', 'coin_settings.*', 'coin_settings.id as coin_setting_id')
            ->where([
                'coin_settings.coin_id' => $coinSetting->coin_id,
                'coin_settings.network' => $coinSetting->network
            ])
            ->first();
    }

    public function createCoinSetting(int $coinId, int $network): CoinSetting
    {
        return CoinSetting::firstOrCreate(['coin_id' => $coinId, 'network' => $network], []);
    }

    public function getContractAddressFromWalletAddress(string $address): ?string
    {
        $contractAddress = $this->model->join('coins', 'coin_settings.coin_id', '=', 'coins.id')
            ->join('wallet_address_histories', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
            ->where('wallet_address_histories.address', $address)
            ->select('coin_settings.contract_address')->first();

        return $contractAddress ? $contractAddress->contract_address : null;
    }

    public function getNetworkFromContractAddress(string $contractAddress): ?int
    {
        $coinSetting = $this->model->join('coins', 'coin_settings.coin_id', '=', 'coins.id')
            ->where('coin_settings.contract_address', $contractAddress)
            ->select('coins.network')
            ->first();

        return $coinSetting ? $coinSetting->network : null;
    }
}
