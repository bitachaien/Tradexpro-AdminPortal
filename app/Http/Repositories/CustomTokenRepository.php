<?php

/**
 * Created by PhpStorm.
 * User: bacchu
 * Date: 1/25/22
 * Time: 5:19 PM
 */

namespace App\Http\Repositories;

use App\Http\Services\ContractAddressCheckerService;
use App\Http\Services\ERC20TokenApi;
use App\Http\Services\NetworkCompareService;
use App\Jobs\AdminTokenReceiveJob;
use App\Model\AdminReceiveTokenTransactionHistory;
use App\Model\Coin;
use App\Model\DepositeTransaction;
use App\Model\EstimateGasFeesTransactionHistory;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use VisualNumberFormat;

use function Amp\call;
use function PHPUnit\Framework\isNull;

class CustomTokenRepository
{
    public function __construct() {}

    // BEP20_TOKEN, ERC20_TOKEN deposit checking
    public function depositCustomToken()
    {
        try {
            $bep20Tokens = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->whereIn('coins.network', [ERC20_TOKEN, BEP20_TOKEN])
                ->where(['coins.status' => STATUS_ACTIVE])
                ->whereNotNull('coin_settings.chain_link')
                ->whereNotNull('coin_settings.contract_address')
                ->get();
            if (isset($bep20Tokens[0])) {
                foreach ($bep20Tokens as $bep20Token) {
                    $this->ecr20TokenDeposit($bep20Token);
                }
            }
            // $erc20Tokens = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            //     ->where(['coins.network' => ERC20_TOKEN])
            //     ->where(['coins.status' => STATUS_ACTIVE])
            //     ->whereNotNull('coin_settings.chain_link')
            //     ->whereNotNull('coin_settings.contract_address')
            //     ->get();
            // if (isset($erc20Tokens[0])) {
            //     foreach($erc20Tokens as $erc20Token) {
            //         $this->ecr20TokenDeposit($erc20Token);
            //     }
            // }
        } catch (\Exception $e) {
            storeException('depositCustomToken ex', $e->getMessage());
        }
    }

    // TRC20 and Polygon Token deposit checking
    public function depositCustomERC20Token()
    {
        storeBotException('depositCustomERC20Token st', 'start');
        try {
            $trc20Tokens = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->whereIn('coins.network', [TRC20_TOKEN, MATIC_TOKEN])
                ->where(['coins.status' => STATUS_ACTIVE])
                ->whereNotNull('coin_settings.chain_link')
                ->whereNotNull('coin_settings.contract_address')
                ->get();
            if (isset($trc20Tokens[0])) {
                foreach ($trc20Tokens as $trc20Token) {
                    $this->ecr20TokenDeposit($trc20Token);
                }
            }
        } catch (\Exception $e) {
            storeException('depositCustomERC20Token ex', $e->getMessage());
        }
    }

    public function ecr20TokenDeposit($coin)
    {
        try {

            storeBotException('TokenDeposit', 'called -> ' . $coin->coin_type);
            $latestTransactions = $this->getLatestTransactionFromBlock($coin);
            $latestTransactionsData = $latestTransactions['data'] ?? collect();
            $latestTransactionsDataResult = $latestTransactionsData?->result ?? [];
            $latestTransactionsBlock = $latestTransactionsData?->block ?? [];
            $last_block_number = $latestTransactionsData?->latest ?? 0;
            storeBotException('$latestTransactions', $latestTransactionsDataResult);

            if ($latestTransactions['success'] == true) {
                if (filled($latestTransactionsDataResult)) {
                    foreach ($latestTransactionsDataResult as $transaction) {
                        storeBotException('coin type =. ', $coin->coin_type);
                        storeBotException('block_number =. ', $transaction->block_number);
                        storeBotException('depositCustomToken single transaction', json_encode($transaction));

                        $this->checkAddressAndDeposit($transaction->to_address, $transaction->tx_hash, $transaction->amount, $transaction->from_address, $coin);
                    }
                }
            } else {
                storeBotException('depositCustomToken', $latestTransactions['message']);
            }

            if (filled($latestTransactionsBlock))
                $this->updateCoinBlockNumber($coin->coin_type, $latestTransactionsBlock, $last_block_number);

            return $latestTransactions;
        } catch (\Exception $e) {
            storeException('ecr20TokenDeposit ex', $e->getMessage());
        }
    }

    public function bep20TokenDeposit($coin)
    {
        try {
            storeBotException('bep20TokenDeposit', 'called -> ' . $coin->coin_type);
            $latestTransactions = $this->getLatestTransactionFromBlock($coin);
            $latestTransactionsData = $latestTransactions['data'] ?? collect();
            $latestTransactionsDataResult = $latestTransactionsData?->result ?? [];
            $latestTransactionsBlock = $latestTransactionsData?->block ?? [];
            $last_block_number = $latestTransactionsData?->latest ?? 0;

            storeBotException('$latestTransactions', $latestTransactionsDataResult);
            if ($latestTransactions['success'] == true) {
                if (filled($latestTransactionsDataResult)) {
                    foreach ($latestTransactionsDataResult as $transaction) {
                        storeBotException('bep20TokenDeposit single transaction', json_encode($transaction));
                        $this->checkAddressAndDeposit($transaction->to_address, $transaction->tx_hash, $transaction->amount, $transaction->from_address, $transaction->block_number, $transaction->block_timestamp);
                    }
                }
            } else {
                storeBotException('depositCustomToken', $latestTransactions['message']);
            }

            if (filled($latestTransactionsBlock))
                $this->updateCoinBlockNumber($coin->coin_type, $latestTransactionsBlock, $last_block_number);

            return $latestTransactions;
        } catch (\Exception $e) {
            storeException('bep20TokenDeposit ex', $e->getMessage());
        }
    }
    // update wallet
    public function updateUserWallet($deposit, $hash)
    {
        try {
            DepositeTransaction::where(['id' => $deposit->id])
                ->update([
                    'status' => STATUS_SUCCESS,
                    //                    'transaction_id' => $hash
                ]);
            $userWallet = $deposit->receiverWallet;
            storeException('depositCustomToken', 'before update wallet balance => ' . $userWallet->balance);
            $userWallet->increment('balance', $deposit->amount);
            storeException('depositCustomToken', 'after update wallet balance => ' . $userWallet->balance);
            storeException('depositCustomToken', 'update one wallet id => ' . $deposit->receiver_wallet_id);
        } catch (\Exception $e) {
            storeException('updateUserWallet ex', $e->getMessage());
        }
    }

    // check address and deposit
    public function checkAddressAndDeposit($address, $hash, $amount, $fromAddress, $coin)
    {
        try {
            storeBotException('deposit address', $address);
            storeBotException('deposit hash', $hash);
            storeBotException('deposit amount', $amount);
            storeBotException('deposit from address', $fromAddress);

            // $checkAddress = WalletAddressHistory::where(['address' => $address])->first();
            $checkAddress = WalletAddressHistory::join('coins', 'wallet_address_histories.coin_type', '=', 'coins.coin_type')
                ->join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where('wallet_address_histories.address', $address)
                ->where('coins.id', $coin->coin_id)
                ->where('coin_settings.contract_address', $coin->contract_address)
                ->first();

            if (!empty($checkAddress)) {

                $checkDeposit = DepositeTransaction::where(['address' => $address, 'transaction_id' => $hash])->first();
                if ($checkDeposit) {
                    storeBotException('checkAddressAndDeposit', 'deposit already in db ' . $hash);
                    $response = ['success' => false, 'message' => __('This hash already in db'), 'data' => []];
                } else {
                    storeBotException('deposit request amount', $amount);
                    $amount = floatval($amount);
                    storeBotException('deposit request amount float', $amount);
                    $createDeposit = DepositeTransaction::create([
                        'address' => $address,
                        'from_address' => $fromAddress,
                        'receiver_wallet_id' => $checkAddress->wallet_id,
                        'address_type' => ADDRESS_TYPE_EXTERNAL,
                        'coin_type' => $checkAddress->coin_type,
                        'amount' => $amount,
                        'transaction_id' => $hash,
                    ]);
                    if ($createDeposit) {
                        storeBotException('deposit', $createDeposit);
                        $wallet = Wallet::where(['id' => $createDeposit->receiver_wallet_id])->first();
                        if ($wallet) {
                            storeException('deposit amount', ($amount));
                            storeException('balance before', $wallet->balance);
                            $wallet->increment('balance', $amount);
                            $createDeposit->status = STATUS_ACTIVE;
                            $createDeposit->save();
                            storeException('balance after', $wallet->balance);
                        }
                        $response = ['success' => true, 'message' => __('New deposit'), 'data' => $createDeposit, 'pk' => $checkAddress->wallet_key];
                    } else {
                        $response = ['success' => false, 'message' => 'deposit credited failed', 'data' => []];
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('This wallet address, network and coin contract address are not related or not found in db'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('checkAddressAndDeposit ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // get latest transaction block data
    public function getLatestTransactionFromBlock($coin)
    {
        $response = ['success' => false, 'message' => 'failed', 'data' => []];
        try {
            $tokenApi = new ERC20TokenApi($coin);
            storeBotException('getLatestTransactionFromBlock coin => ', $coin->coin_type);
            $result = $tokenApi->getContractTransferEvent();
            if ($result['success'] == true) {
                $response = ['success' => $result['success'], 'message' => $result['message'], 'data' => $result['data']];
            } else {
                $response = ['success' => false, 'message' => __('No transaction found'), 'data' => $result['data'] ?? []];
            }
        } catch (\Exception $e) {
            storeException('getLatestTransactionFromBlock ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // check estimate gas for sending token
    public function checkEstimateGasFees($coin, $address, $amount)
    {
        $response = ['success' => false, 'message' => 'failed', 'data' => []];
        try {
            $requestData = [
                "amount_value" => $amount,
                "from_address" => $address,
                "to_address" => $coin->wallet_address
            ];
            $tokenApi = new ERC20TokenApi($coin);
            $check = $tokenApi->checkEstimateGas($requestData);
            storeBotException('checkEstimateGasFees', $check);
            if ($check['success'] == true) {
                $response = ['success' => true, 'message' => $check['message'], 'data' => $check['data']];
            } else {
                $response = ['success' => false, 'message' => $check['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('checkEstimateGasFees ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }

        return $response;
    }

    // send estimate gas fees to address
    public function sendFeesToUserAddress($coin, $address, $amount, $wallet_id, $depositId, $type = null)
    {
        try {
            $requestData = [
                "amount_value" => $amount,
                "from_address" => $coin->wallet_address,
                "to_address" => $address,
                "contracts" => decryptId($coin->wallet_key)
            ];
            $tokenApi = new ERC20TokenApi($coin);
            $result = $tokenApi->sendEth($requestData);
            storeBotException('sendFeesToUserAddress result ', $result);
            if ($result['success'] == true) {
                $this->saveEstimateGasFeesTransaction($wallet_id, $result['data']->hash, $amount, $coin->wallet_address, $address, $depositId, $coin->contract_coin_name, $type);
                $response = ['success' => true, 'message' => __('Fess send successfully'), 'data' => []];
            } else {
                $response = ['success' => false, 'message' => $result['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('sendFeesToUserAddress ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // save estimate gas fees transaction
    public function saveEstimateGasFeesTransaction($wallet_id, $hash, $amount, $adminAddress, $userAddress, $depositId, $contractCoinName, $type = null)
    {
        try {
            $data = EstimateGasFeesTransactionHistory::create([
                'unique_code' => uniqid() . date('') . time(),
                'wallet_id' => $wallet_id,
                'deposit_id' => $depositId,
                'amount' => $amount,
                'coin_type' => $contractCoinName,
                'admin_address' => $adminAddress,
                'user_address' => $userAddress,
                'transaction_hash' => $hash,
                'status' => STATUS_PENDING,
                'type' => $type ?? TYPE_DEPOSIT
            ]);
            //            storeException('saveEstimateGasFeesTransaction', json_encode($data));
        } catch (\Exception $e) {
            storeException('saveEstimateGasFeesTransaction ex', $e->getMessage());
        }
    }

    // receive token from user address
    public function receiveTokenFromUserAddress($coin, $address, $amount, $userPk, $depositId)
    {
        try {
            $requestData = [
                "amount_value" => $amount,
                "from_address" => $address,
                "to_address" => $coin->wallet_address,
                "contracts" => $userPk
            ];

            $checkAddressBalanceAgain = $this->checkWalletAddressAllBalance($coin, $address);
            //            storeBotException('receiveTokenFromUserAddress  $check Address All Balance ',$checkAddressBalanceAgain);
            $tokenApi = new ERC20TokenApi($coin);
            $result = $tokenApi->sendCustomToken($requestData);
            storeBotException('receiveTokenFromUserAddress $result', $result);
            if ($result['success'] == true) {
                $this->saveReceiveTransaction($result['data']->used_gas, $result['data']->hash, $amount, $coin->wallet_address, $address, $depositId);
                $response = ['success' => true, 'message' => __('Token received successfully'), 'data' => $result['data']];
            } else {
                $response = ['success' => false, 'message' => $result['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('receiveTokenFromUserAddress ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // save receive token transaction
    public function saveReceiveTransaction($fees, $hash, $amount, $adminAddress, $userAddress, $depositId, $type = null)
    {
        try {
            $data = AdminReceiveTokenTransactionHistory::create([
                'unique_code' => uniqid() . date('') . time(),
                'amount' => $amount,
                'deposit_id' => $depositId,
                'fees' => $fees,
                'to_address' => $adminAddress,
                'from_address' => $userAddress,
                'transaction_hash' => $hash,
                'status' => STATUS_SUCCESS,
                'type' => $type ?? TYPE_DEPOSIT
            ]);
            //            storeBotException('saveReceiveTransaction', json_encode($data));
        } catch (\Exception $e) {
            storeException('saveReceiveTransaction', $e->getMessage());
        }
    }

    // check wallet balance
    public function checkWalletAddressBalance($coin, $address, $type = 1)
    {
        try {
            $requestData = array(
                "type" => $type,
                "address" => $address,
            );
            $tokenApi = new ERC20TokenApi($coin);
            storeBotException('$requestData balance', $requestData);
            $result = $tokenApi->checkWalletBalance($requestData);
            if ($result['success'] == true) {
                $response = ['success' => true, 'message' => __('Get balance'), 'data' => $result['data']];
            } else {
                storeException('sendFeesToUserAddress', $result['message']);
                $response = ['success' => false, 'message' => $result['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('checkWalletAddressBalance ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // check wallet balance
    public function checkWalletAddressAllBalance($coin, $address)
    {
        try {
            $requestData = array(
                "type" => 3,
                "address" => $address,
            );
            $tokenApi = new ERC20TokenApi($coin);
            $result = $tokenApi->checkWalletBalance($requestData);
            storeBotException('checkWalletAddressAllBalance check', $result);
            if ($result['success'] == true) {
                $response = ['success' => true, 'message' => __('Get balance'), 'data' => $result['data']];
            } else {
                storeException('sendFeesToUserAddress', $result['message']);
                $response = ['success' => false, 'message' => $result['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('checkWalletAddressBalance ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // token receive manually by admin
    public function tokenReceiveManuallyByAdmin($transaction, $adminId)
    {
        storeBotException('tokenReceiveManuallyByAdmin', 'called for ' . $transaction->coin_type);
        try {
            return $this->tokenReceiveManuallyByAdminProcess($transaction, $adminId);
        } catch (\Exception $e) {
            storeException('tokenReceiveManuallyByAdmin ex', $e->getMessage());
            return responseData(false);
        }
    }

    public function getTronEstimateGas($transaction, $coin)
    {
        try {
            // if(isset($coin->gas_limit) && $coin->gas_limit !== 0) return TRC20ESTFEE;
            storeBotException('TronEstimateGas', 'start');
            $tokenApi = new ERC20TokenApi($coin);
            $requestData = [
                "gas_limit"   => $coin->gas_limit,
                "to_wallet"   => $coin->wallet_address,
                "from_wallet"   => $transaction->address,
                "amount"      => $transaction->amount,
            ];
            $response = $tokenApi->getTrxEstimatedGas($requestData);
            if (isset($response['success']) && $response['success']) {
                storeBotException('getTronEstimateGas result', json_encode($response['data']));
                if (isset($response['data'])) {
                    return responseData(true, $response['message'], $response['data']);
                }
            } else {
                storeException('getTronEstimateGas failed', $response['message']);
                return responseData(false, $response['message']);
            }
        } catch (\Exception $e) {
            storeBotException('tokenReceiveManuallyByAdminProcess getTronEstimateGas', $e->getMessage());
            return responseData(false, $e->getMessage());
        }
        return responseData(false, __('Something went wrong'));
    }

    // token Receive Manually By Admin process
    public function tokenReceiveManuallyByAdminProcess($transaction, $adminId)
    {
        storeBotException('tokenReceiveManuallyByAdminProcess', 'start process for ' . $transaction->coin_type);
        try {
            if ($transaction->is_admin_receive == STATUS_PENDING) {
                $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                    ->where(['coins.coin_type' => $transaction->coin_type])
                    ->first();
                if (!$coin) {
                    return responseData(false, __('Coin not found'));
                }

                $sendAmount = (float)$transaction->amount;
                if (empty($coin->wallet_address)) {
                    return responseData(false, __('System wallet address not found'));
                }
                $checkAddress = $this->checkAddress($transaction->address, $coin->coin_type);
                if (empty($checkAddress)) {
                    return responseData(false, __('User wallet address not found'));
                }

                $userPk = get_wallet_personal_add($transaction->address, $checkAddress->wallet_key);

                if ($coin->network == TRC20_TOKEN) {
                    $checkGasFees = $this->getTronEstimateGas($transaction, $coin);
                } else {
                    $checkGasFees = $this->checkEstimateGasFees($coin, $transaction->address, $sendAmount);
                }

                $gas = 0;
                if ($checkGasFees['success'] == true) {
                    $tronGas = $checkGasFees['data']->estimateGasFees;
                    storeBotException('Estimate gas ', $tronGas);
                    if ($coin->network != TRC20_TOKEN) {
                        $estimateFees = number_format($tronGas, 18);
                        $gas = bcaddx($estimateFees, (bcdivx(bcmulx($estimateFees, 10, 18), 100, 18)), 18);
                    }
                    if ($coin->network == TRC20_TOKEN) {
                        $gas = $tronGas + 2;
                    }
                    storeBotException('Gas fees ', $gas);
                    $checkAddressBalance = $this->checkWalletAddressBalance($coin, $transaction->address, 3);

                    if ($checkAddressBalance['success'] == true) {
                        $walletNetBalance = $checkAddressBalance['data']->net_balance;
                        $walletTokenBalance = $checkAddressBalance['data']->token_balance;
                        storeBotException('$walletTokenBalance', $walletTokenBalance);
                        storeBotException('$walletNetBalance', $walletNetBalance);
                        if ($sendAmount > $walletTokenBalance) {
                            return responseData(false, __('User wallet does not have enough token. Current token balance is ') . $walletTokenBalance . __(' but transaction balance is ') . $sendAmount);
                        }
                        if ($walletNetBalance >= $gas) {
                            $estimateGas = 0;
                            storeBotException('$estimateGas 0 ', $estimateGas);
                        } else {
                            storeBotException('$estimateGas bcsub gas ', $gas);
                            storeBotException('$estimateGas bcsub walletNetBalance ', number_format($walletNetBalance, 18));
                            $estimateGas = bcsubx($gas, number_format($walletNetBalance, 18), 18);
                            if ($coin->network == TRC20_TOKEN) {
                                if ($estimateGas <= 0.000001) $estimateGas = "0.000001";
                            }
                            storeBotException('$estimateGas have ', $estimateGas);
                        }
                        storeBotException('estimateGas => ', $estimateGas);

                        if ($estimateGas > 0) {
                            storeBotException('sendFeesToUserAddress ', $estimateGas);
                            // before send fees need to check system wallet balance
                            $checkSystemWalletBalance = $this->checkWalletAddressBalance($coin, $coin->wallet_address, 1);
                            storeBotException('checkSystemWalletBalance', $checkSystemWalletBalance);
                            if ($estimateGas > $checkSystemWalletBalance['data']->net_balance) {
                                return responseData(false, __('System wallet did not have enough native balance for fees . Current balance is ') . $checkSystemWalletBalance['data']->net_balance . ' needed fees is ' . $estimateGas);
                            }
                            $sendFees = $this->sendFeesToUserAddress($coin, $transaction->address, $estimateGas, $checkAddress->wallet_id, $transaction->id, TYPE_DEPOSIT);
                            if ($sendFees['success'] == true) {
                                storeBotException('tokenReceiveManuallyByAdminProcess -> ', 'sendFeesToUserAddress success . the next process will held on getDepositBalanceFromUserJob');
                            } else {
                                storeException('tokenReceiveManuallyByAdminProcess', $sendFees['message']);
                                return responseData(false, $sendFees['message']);
                            }
                        } else {
                            storeBotException('sendFeesToUserAddress ', 'no gas needed');
                            $checkAddressBalanceAgain2 = $this->checkWalletAddressBalance($coin, $transaction->address);
                            storeBotException('tokenReceiveManuallyByAdminProcess  $checkAddressBalanceAgain2', $checkAddressBalanceAgain2);
                            if ($checkAddressBalanceAgain2['success'] == true) {
                                storeException('tokenReceiveManuallyByAdminProcess', 'next process goes to AdminTokenReceiveJob queue');
                            } else {
                                storeException('tokenReceiveManuallyByAdminProcess', 'again 2 get balance failed');
                            }
                        }
                        storeException('tokenReceiveManuallyByAdminProcess', 'next process receiveTokenFromUserAddressByAdminPanel');
                        $checkAddressBalanceBeforeTake = $this->checkWalletAddressBalance($coin, $transaction->address, 1);
                        storeBotException('$checkAddressBalanceBeforeTake', $checkAddressBalanceBeforeTake);
                        if ($checkAddressBalance['success'] == true) {
                            if ($estimateGas > $checkAddressBalanceBeforeTake['data']->net_balance) {
                                return responseData(false, __('User wallet did not have enough native balance for fees . Current balance is ') . $checkAddressBalanceBeforeTake['data']->net_balance . ' needed fees is ' . $estimateGas);
                            }

                            $receiveToken = $this->receiveTokenFromUserAddressByAdminPanel($gas, $coin, $transaction->address, $sendAmount, $userPk, $transaction->id, TYPE_DEPOSIT);
                            if ($receiveToken['success'] == true) {
                                $this->updateUserWalletByAdmin($transaction, $adminId);
                                storeException('tokenReceiveManuallyByAdminProcess success', $receiveToken['message']);
                                return responseData(true, __('Admin token received successfully'));
                            } else {
                                return responseData(false, $receiveToken['message']);
                                storeException('tokenReceiveManuallyByAdminProcess failed', $receiveToken['message']);
                            }
                            storeException('tokenReceiveManuallyByAdminProcess', 'token received process executed');
                        } else {
                            return responseData(false, $checkAddressBalanceBeforeTake['message']);
                        }
                    } else {
                        storeException('tokenReceiveManuallyByAdminProcess', $checkAddressBalance['message']);
                        return responseData(false, $checkAddressBalance['message']);
                    }
                } else {
                    storeException('tokenReceiveManuallyByAdminProcess', $checkGasFees['message']);
                    return responseData(false, $checkGasFees['message']);
                }
            } else {
                storeException('tokenReceiveManuallyByAdminProcess', 'transaction is already received by admin');
                return responseData(false, __('Transaction is already received by admin'));
            }
        } catch (\Exception $e) {
            storeException('tokenReceiveManuallyByAdminProcess', $e->getMessage());
            return responseData(false, $e->getMessage());
        }
        return responseData(false, __('Something went wrong'));
    }

    // check address
    public function checkAddress($address, $coin_type = "")
    {
        if (empty($coin_type)) {
            return WalletAddressHistory::where(['address' => $address])->first();
        } else {
            return WalletAddressHistory::where(['address' => $address, 'coin_type' => $coin_type])->first();
        }
    }

    // receive token from user address by admin
    public function receiveTokenFromUserAddressByAdminPanel($gas, $coin, $address, $amount, $userPk, $depositId, $type = null)
    {
        try {
            storeBotException('type', $type);
            $requestData = [
                "amount_value" => $amount,
                "from_address" => $address,
                "to_address" => $coin->wallet_address,
                "contracts" => $userPk
            ];
            $checkAddressBalanceAgain = $this->checkWalletAddressAllBalance($coin, $address);
            storeBotException('receiveTokenFromUserAddressByAdminPanel  $check Address All Balance ', $checkAddressBalanceAgain);
            if ($checkAddressBalanceAgain['success'] == true) {
                // $netGasBalance = bcaddx((int)$checkAddressBalanceAgain['data']->net_balance,0,18);
                // storeException('receiveTokenFromUserAddressByAdminPanel netGasBalance', $netGasBalance);

                // if ($gas > $netGasBalance) {
                //     storeException('receiveTokenFromUserAddressByAdminPanel need gas', $gas);
                //     storeException('receiveTokenFromUserAddressByAdminPanel need gas', 'Do not have enough gas');
                //     $response = ['success' => false, 'message' => __('Do not have enough gas balance'), 'data' => []];
                // } else {
                if ($amount > $checkAddressBalanceAgain['data']->token_balance) {
                    storeException('receiveTokenFromUserAddressByAdminPanel need token', $amount);
                    storeException('receiveTokenFromUserAddressByAdminPanel need token', 'Do not have enough token balance');
                    $response = ['success' => false, 'message' => __('Do not have enough token balance'), 'data' => []];
                } else {
                    storeException('send token', 'ready to go');
                    $tokenApi = new ERC20TokenApi($coin);
                    $result = $tokenApi->sendCustomToken($requestData);
                    storeException('receiveTokenFromUserAddressByAdminPanel $result', $result);
                    if ($result['success'] == true) {
                        $this->saveReceiveTransaction($result['data']->used_gas, $result['data']->hash, $amount, $coin->wallet_address, $address, $depositId, $type);
                        storeException('receiveTokenFromUserAddressByAdminPanel', 'Token receive success');
                        $response = ['success' => true, 'message' => __('Token received successfully'), 'data' => $result['data']];
                    } else {
                        storeException('receiveTokenFromUserAddressByAdminPanel', $result['message']);
                        $response = ['success' => false, 'message' => $result['message'], 'data' => []];
                    }
                }
                // }

            } else {
                storeException('receiveTokenFromUserAddressByAdminPanel', $checkAddressBalanceAgain['message']);
                $response = ['success' => false, 'message' => $checkAddressBalanceAgain['message'], 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('receiveTokenFromUserAddressByAdminPanel ex', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // update wallet
    public function updateUserWalletByAdmin($deposit, $adminId)
    {
        try {
            DepositeTransaction::where(['id' => $deposit->id])
                ->update([
                    'is_admin_receive' => STATUS_SUCCESS,
                    'received_amount' => $deposit->amount,
                    'updated_by' => $adminId
                ]);

            storeException('updateUserWalletByAdmin', 'Deposit process success');
        } catch (\Exception $e) {
            storeException('updateUserWalletByAdmin', $e->getMessage());
        }
    }


    // get deposit token balance from user
    public function getDepositTokenFromUser()
    {
        storeBotException('getDepositTokenFromUser command', 'called');
        try {
            $adminId = 1;
            $admin = User::where(['role' => USER_ROLE_ADMIN])->orderBy('id', 'asc')->first();
            if ($admin) {
                $adminId = $admin->id;
            }
            $transactions = DepositeTransaction::join('coins', 'coins.coin_type', '=', 'deposite_transactions.coin_type')
                ->where(['deposite_transactions.address_type' => ADDRESS_TYPE_EXTERNAL])
                ->where('deposite_transactions.is_admin_receive', STATUS_PENDING)
                ->select('deposite_transactions.*')
                ->whereIn('coins.network', [ERC20_TOKEN, BEP20_TOKEN, TRC20_TOKEN])
                ->get();
            if (isset($transactions[0])) {
                foreach ($transactions as $transaction) {
                    $this->tokenReceiveManuallyByAdmin($transaction, $adminId);
                }
            }
        } catch (\Exception $e) {
            storeException('getDepositTokenFromUser ex', $e->getMessage());
        }
    }


    // token Receive Manually By Admin process
    public function tokenReceiveManuallyByAdminFromBuyToken($transaction, $adminId)
    {
        storeBotException('tokenReceiveManuallyByAdminFromBuyToken', 'start process');
        try {
            if ($transaction->is_admin_receive == STATUS_PENDING) {
                $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                    ->where(['coins.id' => $transaction->coin_id])
                    ->first();
                $sendAmount = (float)$transaction->amount;
                $checkAddress = $this->checkAddress($transaction->address);
                $userPk = get_wallet_personal_add($transaction->address, $checkAddress->wallet_key);
                if ($coin->network == TRC20_TOKEN) {
                    $checkGasFees['success'] = true;
                    $gas = TRC20ESTFEE;
                } else {
                    $checkGasFees = $this->checkEstimateGasFees($coin, $transaction->address, $sendAmount);
                }
                storeBotException('$checkGasFees', $checkGasFees);
                if ($checkGasFees['success'] == true) {
                    if ($coin->network != TRC20_TOKEN) {
                        storeException('Estimate gas ', $checkGasFees['data']->estimateGasFees);
                        $estimateFees = $checkGasFees['data']->estimateGasFees;
                        $gas = bcaddx($estimateFees, (bcdivx(bcmulx($estimateFees, 10, 8), 100, 8)), 8);
                        storeException('Gas', $gas);
                    }
                    $checkAddressBalance = $this->checkWalletAddressBalance($coin, $transaction->address, 1);
                    if ($checkAddressBalance['success'] == true) {
                        $walletNetBalance = $checkAddressBalance['data']->net_balance;
                        storeException('$walletNetBalance', $walletNetBalance);
                        if ($walletNetBalance >= $gas) {
                            $estimateGas = 0;
                            storeException('$estimateGas 0 ', $estimateGas);
                        } else {
                            storeException('$estimateGas bcsub gas ', $gas);
                            storeException('$estimateGas bcsub walletNetBalance ', number_format($walletNetBalance, 18));
                            $estimateGas = bcsubx($gas, number_format($walletNetBalance, 18), 8);
                            storeException('$estimateGas have ', $estimateGas);
                        }
                        if ($estimateGas > 0) {
                            storeException('sendFeesToUserAddress ', $estimateGas);
                            $sendFees = $this->sendFeesToUserAddress($coin, $transaction->address, $estimateGas, $checkAddress->wallet_id, $transaction->id, TYPE_BUY);
                            if ($sendFees['success'] == true) {
                                storeException('tokenReceiveManuallyByAdminFromBuyToken -> ', 'sendFeesToUserAddress success . the next process will held on getDepositBalanceFromUserJob');
                            } else {
                                storeException('tokenReceiveManuallyByAdminFromBuyToken', 'send fees process failed');
                            }
                        } else {
                            storeException('sendFeesToUserAddress ', 'no gas needed');
                            $checkAddressBalanceAgain2 = $this->checkWalletAddressBalance($coin, $transaction->address);
                            storeException('tokenReceiveManuallyByAdminFromBuyToken  $checkAddressBalanceAgain2', $checkAddressBalanceAgain2);
                            if ($checkAddressBalanceAgain2['success'] == true) {
                                storeException('tokenReceiveManuallyByAdminFromBuyToken', 'next process goes to AdminTokenReceiveJob queue');
                            } else {
                                storeException('tokenReceiveManuallyByAdminFromBuyToken', 'again 2 get balance failed');
                            }
                        }
                        storeException('tokenReceiveManuallyByAdminFromBuyToken', 'next process receiveTokenFromUserAddressByAdminPanel');

                        $receiveToken = $this->receiveTokenFromUserAddressByAdminPanel($gas, $coin, $transaction->address, $sendAmount, $userPk, $transaction->id, TYPE_BUY);
                        if ($receiveToken['success'] == true) {
                            DB::table('token_buy_histories')->where(['id' => $transaction->id])
                                ->update(['is_admin_receive' => STATUS_ACTIVE]);
                        } else {
                            storeException('tokenReceiveManuallyByAdminFromBuyToken', 'token received process failed');
                        }
                        storeException('tokenReceiveManuallyByAdminFromBuyToken', 'token received process executed');
                    } else {
                        storeException('tokenReceiveManuallyByAdminFromBuyToken', 'get balance failed');
                    }
                } else {
                    storeException('tokenReceiveManuallyByAdminFromBuyToken', 'check gas fees calculate failed');
                }
            } else {
                storeException('tokenReceiveManuallyByAdminFromBuyToken', 'transaction is already received by admin');
            }
        } catch (\Exception $e) {
            storeException('tokenReceiveManuallyByAdminFromBuyToken', $e->getMessage());
        }
    }

    public function updateCoinBlockNumber($coin_type, $transactionBlockData, $last_block_number = 0)
    {
        try {
            $coin = Coin::where('coin_type', $coin_type)->first();
            if (isset($coin)) {
                storeBotException('last timestamp', $coin->last_timestamp);
                storeBotException('last block number', $coin->last_block_number);

                $coin->from_block_number = $transactionBlockData->from_block_number;
                $coin->to_block_number = $transactionBlockData->to_block_number;

                if ($last_block_number) $coin->last_block_number = $last_block_number;
                $coin->save();

                storeBotException('last timestamp', $coin->last_timestamp);
                storeBotException('last block number', $coin->last_block_number);
            }
        } catch (\Exception $e) {
            storeException('updateCoinBlockNumber', $e->getMessage());
        }
    }
}
