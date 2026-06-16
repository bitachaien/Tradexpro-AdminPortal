<?php


namespace App\Http\Services;


use App\Model\Coin;
use App\Model\DepositeTransaction;
use Modules\IcoLaunchpad\Http\Services\ERC20TokenApiService;

class DepositService
{

    public function __construct() {}

    // check deposit by transaction
    public function checkDepositByHash($network, $coinType, $hash, $type)
    {
        try {
            $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $coinType])
                ->first();
            if ($coin) {
                if ($network == $coin->network) {
                    if ($coin->network == BITGO_API) {
                        $response = $this->checkBitgoTransaction($coin, $hash, $type);
                    } elseif ($coin->network == BEP20_TOKEN || $coin->network == ERC20_TOKEN || $coin->network == MATIC_TOKEN) {
                        $response = $this->checkERC20Transaction($coin, $hash, $type);
                    } elseif ($coin->network == TRC20_TOKEN) {
                        $response = $this->checkTRC20Transaction($coin, $hash, $type);
                    } else {
                        $response = responseData(false, __('This feature is currently under construction'));
                    }
                } else {
                    $response = responseData(false, __('Your selected Coin API is invalid for this coin'));
                }
            } else {
                $response = responseData(false, __('Coin not found'));
            }
        } catch (\Exception $e) {
            storeException('checkDepositByHash', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }

    // check bitgo transaction hash
    public function checkBitgoTransaction($coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->bitgo_wallet_id)) {
                $response = responseData(false, __('Bitgo wallet id is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('bitgoWalletCoinDeposit hash already in db'));
                } else {
                    $getTransaction = $service->getTransaction($coin->coin_type, $coin->bitgo_wallet_id, $hash);
                    if ($getTransaction['success'] == true) {
                        $bitgoService = new BitgoWalletService();
                        $transactionData = $getTransaction['data'];
                        if ($transactionData['type'] == 'receive' && $transactionData['state'] == 'confirmed') {
                            $coinVal = $bitgoService->getDepositDivisibilityValues($transactionData['coin']);
                            $amount = bcdivx($transactionData['value'], $coinVal, 8);

                            $data = [
                                'coin_type' => $transactionData['coin'],
                                'txId' => $transactionData['txid'],
                                'confirmations' => $transactionData['confirmations'],
                                'amount' => $amount
                            ];

                            if (isset($transactionData['entries'][0])) {
                                foreach ($transactionData['entries'] as $entry) {
                                    if (isset($entry['wallet']) && ($entry['wallet'] == $transactionData['wallet'])) {
                                        $data['address'] = $entry['address'];
                                        storeException('entry address', $data['address']);
                                    }
                                }
                            }
                            if (isset($data['address'])) {
                                if ($type == CHECK_DEPOSIT) {
                                    $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                                } else {
                                    $response = $service->checkAddressAndDeposit($data);
                                }
                            } else {
                                $response = ['success' => false, 'message' => __('No address found')];
                            }
                        } else {
                            $response = ['success' => false, 'message' => __('The transaction type is not receive')];
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkBitgoTransaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }


    // check erc20 transaction hash
    public function checkERC20Transaction($coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->chain_link)) {
                $response = responseData(false, __('Chain link is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('Transaction hash already in db'));
                } else {
                    $erc20Api = new ERC20TokenApi($coin);
                    $reqData = ['transaction_hash' => $hash, 'contract_address' => $coin->contract_address];
                    $getTransaction = $erc20Api->getTransactionData($reqData);
                    // dd($getTransaction);
                    if ($getTransaction['success'] == true) {
                        $transactionData = $getTransaction['data'];
                        $data = [
                            'coin_type' => $coin->coin_type,
                            'txId' => $transactionData->txID,
                            'confirmations' => 1,
                            'amount' => $transactionData->amount,
                            'address' => $transactionData->toAddress,
                            'from_address' => $transactionData->fromAddress
                        ];

                        if ($type == CHECK_DEPOSIT) {
                            $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                        } else {
                            $response = $service->checkAddressAndDeposit($data);
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkBitgoTransaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }
    public function checkTRC20Transaction($coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->chain_link)) {
                $response = responseData(false, __('Chain link is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('Transaction hash already in db'));
                } else {
                    $erc20Api = new ERC20TokenApi($coin);
                    $reqData = ['transaction_hash' => $hash, 'contract_address' => $coin->contract_address];
                    $getTransaction = $erc20Api->getTrxTransaction($reqData);
                    if ($getTransaction['success'] == true) {
                        $transactionData = $getTransaction['data'];
                        $data = [
                            'coin_type' => $coin->coin_type,
                            'txId' => $transactionData->transaction,
                            'confirmations' => 1,
                            'amount' => ($transactionData->result->value / 1000000),
                            'address' => $transactionData->result->to,
                            'from_address' => $transactionData->result->from
                        ];

                        if ($type == CHECK_DEPOSIT) {
                            $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                        } else {
                            $response = $service->checkAddressAndDeposit($data);
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkTRC20Transaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }
}
