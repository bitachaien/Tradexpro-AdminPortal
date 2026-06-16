<?php
namespace App\Exports;

use App\Model\Buy;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BuyOrderHistory implements FromCollection, WithHeadings
{
    public function __construct(
        public $request
    )
    {

    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try{
            $data = Buy::select(DB::raw("users.email as email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price,
                            amount,processed,visualNumberFormat(TRUNCATE((amount - processed), 8)) as remaining,  buys.status, buys.created_at"))
                ->join('users',['users.id' => 'buys.user_id'])
                ->join('coins as base_coin_table',['base_coin_id' => 'base_coin_table.id'])
                ->join('coins as trade_coin_table',['trade_coin_id' => 'trade_coin_table.id'])
                ->where(['request_amount' => 0,'is_market' => 0])
                ->withTrashed();
            if(isset($this->request->from_date) && isset($this->request->to_date)){
                $data = $data->whereBetween('created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
            }
            $data = $data->get();
            $data->map(function($data){
                $data->price = $data->price .' '.$data->base_coin;
                $data->amount = $data->amount .' '.$data->trade_coin;
                $data->processed = $data->processed .' '.$data->trade_coin;
                $data->remaining = $data->remaining .' '.$data->trade_coin;
                if($data->status == 1) {
                    $data->status = __('Success');
                } elseif($data->deleted_at != null) {
                    $data->status = __('Processing');
                } elseif($data->status == 0) {
                    $data->status = __('Pending');
                } else {
                    $data->status = __('Deleted');
                }
            });
            return $data;
        }catch(\Exception $e)  {
            storeException('All buy trade Export', $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            __("User"), __("Base Coin"), __("Trade Coin"), __('Price'), __('Amount'), __("Processed"), __("Remaining"), __("Status"), __("Created At")
        ];

    }
}