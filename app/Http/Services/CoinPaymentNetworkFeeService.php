<?php
namespace App\Http\Services;


use App\Http\Repositories\CoinPaymentNetworkFeeRepository;
use App\Model\Coin;
use App\Model\CoinPaymentNetworkFee;
use Carbon\Carbon;

class CoinPaymentNetworkFeeService extends BaseService
{
    public $model = CoinPaymentNetworkFee::class;
    public $repository = CoinPaymentNetworkFeeRepository::class;
    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function  getCoinPaymentNetworkFeeList()
    {
        try{
            $data = $this->object->getCoinPaymentNetworkFeeList();
            $response = ['success' => true, 'message' => __('Coin payment API fee list'), 'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getCoinPaymentNetworkFeeList",$e->getMessage());
        }

        return $response;
    }

    public function CreateOrUpdate()
    {
           try {
               $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                   ->where('coins.network', COIN_PAYMENT)->first();
               if ($coin) {
                   $api = new CoinPaymentsAPI();
                   $rates = $api->GetRates();
                   if (is_array($rates) && isset($rates['error']) && ($rates['error'] == 'ok')) {
                       CoinPaymentNetworkFee::query()->truncate();
                       $records = [];
                       foreach ($rates['result'] as $type => $row){
                           $records[] = [
                               'coin_type' => $type,
                               'is_fiat' => $row['is_fiat'],
                               'last_update' => date('Y-m-d H:i:s',$row['last_update']),
                               'status' => $row['status'],
                               'tx_fee' => $row['tx_fee'],
                               'rate_btc' => $row['rate_btc'],
                               'created_at' => Carbon::now(),
                               'updated_at' => Carbon::now(),
                           ];
                       }
                       CoinPaymentNetworkFee::insert($records);
                       $response = [ 'success' => true, 'message' => __('CoinPayment Network fees Sync Successfully'), 'date' => [] ];
                   } else {
                       $response = [ 'success' => false, 'message' => $rates['error'], 'date' => [] ];;
                   }
               } else {
                   $response = [ 'success' => false, 'message' => __('No coin found with coin payment network'), 'date' => [] ];;
               }
           } catch (\Exception $e){
                storeException("Sync CoinPayment Network fees : ",$e->getMessage());
               $response = [ 'success' => false, 'message' => __('CoinPayment Network fees Sync failed'), 'date' => [] ];;
           }
           return $response;
    }

}
