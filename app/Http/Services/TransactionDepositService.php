<?php
namespace App\Http\Services;

use Exception;
use App\Model\Coin;
use App\Model\Wallet;
use App\Jobs\TransactionDeposit;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\DB;
use App\Model\WalletAddressHistory;
use App\Exceptions\InvalidRequestException;
use App\Http\Resources\DepositeTransactionResource;

class TransactionDepositService
{
    public function __construct()
    {
    }

    public function getNetworks(): array
    {
        $responseData = [];
        $networks = function_exists('selected_node_network') ? selected_node_network() : [];
        foreach ($networks as $key => $value) {
            $responseData[] = [
                "id" => $key,
                "name" => $value,
            ];
        }
        return responseData(true, __("Networks get successfully"), $responseData);
    }

    public function getCoinNetwork($request): array
    {
        if (!isset($request->network_id))
            return responseData(false, __("No network found"));
        if (!is_numeric($request->network_id))
            return responseData(false, __("Network is invalid"));

        $coins = Coin::where("network", $request->network_id)->get(["id", "name", "coin_type", "network as network_id"]);
        return responseData(true, __("Network coins get successfully"), $coins);
    }

    public function checkCoinTransactionAndDeposit($request): array
    {
        $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.id' => $request->coin_id])
            ->first();

        // return if coin not exist
        if (!$coin)
            throw new InvalidRequestException(__("Coin not found"));

        $deposit = DepositeTransaction::where("transaction_id", $request->trx_id)->first();
        if ($deposit)
            return responseData(true, __("This transaction already deposited in our system"), new DepositeTransactionResource($deposit));

        $erc20Api = new ERC20TokenApi($coin);
        $getTransaction = $erc20Api->getTransactionData([
            'transaction_hash' => $request->trx_id,
            'contract_address' => $coin->contract_address
        ]);
        if ($getTransaction["success"] == false)
            throw new InvalidRequestException($getTransaction["message"]);

        $transactionData = (array) $getTransaction['data'];
        $data = [
            'coin_type' => $coin->coin_type,
            'txId' => $transactionData['txID'],
            'confirmations' => STATUS_ACTIVE,
            'amount' => truncate_num($transactionData['amount']),
            'address' => $transactionData['toAddress'],
            'from_address' => $transactionData['fromAddress']
        ];
        $responseData = $data;
        $responseData["network"] = selected_node_network($coin->network);

        $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
        if (empty($checkAddress))
            return responseData(true, __("Transaction details found but To address not match in system"), $responseData);

        $wallet = Wallet::find($checkAddress->wallet_id);
        if (empty($wallet))
            return responseData(true, __("This transaction already deposited in our system"), $responseData);

        TransactionDeposit::dispatch($data)->onQueue("deposit");
        return responseData(true, __("Transaction details found, System will adjust deposit soon"), $responseData);
    }

    public function checkAddressAndDeposit($data)
    {
        $deposit = DepositeTransaction::where("transaction_id", $data['txId'])->first();
        if ($deposit)
            throw new InvalidRequestException(__('Transaction already exist'));

        $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
        if (empty($checkAddress))
            throw new InvalidRequestException('This address not found in db the address is ' . $data['address']);

        $wallet = Wallet::find($checkAddress->wallet_id);
        if (empty($wallet))
            throw new InvalidRequestException(__('wallet not found'));

        DB::beginTransaction();
        try {
            DepositeTransaction::create($this->depositData($data, $wallet));
            $wallet->increment('balance', $data['amount']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return responseData(true, __('Wallet deposited successfully'));
    }
    // deposit data
    public function depositData($data, $wallet)
    {
        return [
            'address' => $data['address'],
            'from_address' => isset($data['from_address']) ? $data['from_address'] : "",
            'receiver_wallet_id' => $wallet->id,
            'address_type' => ADDRESS_TYPE_EXTERNAL,
            'coin_type' => $wallet->coin_type,
            'amount' => $data['amount'],
            'transaction_id' => $data['txId'],
            'status' => STATUS_SUCCESS,
            'confirmations' => $data['confirmations']
        ];
    }
}