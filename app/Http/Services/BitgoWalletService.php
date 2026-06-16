<?php
namespace App\Http\Services;


use Illuminate\Support\Facades\Schema;
use Sdtech\BitgoApiLaravel\Service\BitgoApiLaravelService;

class BitgoWalletService {

    private $apiService;

    public function __construct()
    {
        if (Schema::hasTable('admin_settings')) {
            $adm_setting = allsetting();

            $bitgo_api = isset($adm_setting['bitgo_api']) ? $adm_setting['bitgo_api'] : '';
            $bitgoExpess = isset($adm_setting['bitgoExpess']) ? $adm_setting['bitgoExpess'] : '';
            $BITGO_ENV = isset($adm_setting['BITGO_ENV']) ? $adm_setting['BITGO_ENV'] : 'live';
            $bitgo_token = isset($adm_setting['bitgo_token']) ? $adm_setting['bitgo_token'] : '';

            config(['bitgolaravelapi.BITGO_API_BASE_URL' => $bitgo_api]);
            config(['bitgolaravelapi.BITGO_API_EXPRESS_URL' => $bitgoExpess]);
            config(['bitgolaravelapi.BITGO_API_ACCESS_TOKEN' => $bitgo_token]);
            config(['bitgolaravelapi.BITGO_ENV' => $BITGO_ENV]);
        }

        $this->apiService = new BitgoApiLaravelService();
    }


    /**
     * Gets the current wallet.
     * @param access_token authorization key
     * @param coin coin type
     * @param walletId wallet id
     */
    public function getBitgoWallet($coinType,$walletId) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }
        return $this->apiService->getWallet(strtolower($coinType),$walletId);
    }
    /**
     * Gets the current wallet.
     * @param access_token authorization key
     * @param coin coin type
     *
     */
    public function getBitgoWalletList($coinType) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }
        return $this->apiService->getWalletList(strtolower($coinType));
    }

    /**
     * create wallet address.
     * @param access_token authorization key
     * @param coin coin type
     * @param walletId wallet id
     * @param label wallet name
     */
    public function createBitgoWalletAddress($coinType,$walletId,$chain,$label=null) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }

        return $this->apiService->createWalletAddress(strtolower($coinType),$walletId,$chain,$label);
    }

    /**
     * transaction data
     * @param access_token authorization key
     * @param coin coin type
     * @param walletId wallet id
     * @param txid transaction id
     */
    public function transferBitgoData($coinType,$walletId,$txId) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }
        return $this->apiService->transferData(strtolower($coinType),$walletId,$txId);
    }

    /**
     * send coin using express
     * @param access_token authorization key
     * @param coin coin type
     * @param walletId wallet id
     * @param amount send amount
     * @param address receipant address
     * @param walletPassphrase wallet password
     */
    public function sendCoinsWithBitgo($coinType,$walletId,$amount,$address,$walletPassphrase) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }

        return $this->apiService->sendCoins(strtolower($coinType),$walletId,$amount,$address,$walletPassphrase);
    }

    public function getDepositDivisibilityValues($coinType)
    {
        if(allsetting('BITGO_ENV') == 'test') {
            $coinType = 't'.$coinType;
        }
        return $this->apiService->getDepositDivisibilityValue(strtolower($coinType));
    }

    public function addWebhook($coin,$walletId,$type,$allToken,$url,$label,$numConfirmations) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coin =  't'.$coin;
        }
        return $this->apiService->addWebhook(strtolower($coin),$walletId,$type,$allToken,$url,$label,$numConfirmations);
    }
    public function removeWalletWebhook($coin,$walletId,$type,$url,$hookId) {
        if(allsetting('BITGO_ENV') == 'test') {
            $coin = 't'.$coin;
        }
        return $this->apiService->removeWalletWebhook(strtolower($coin),$walletId,$type,$url,$hookId);
    }
}



