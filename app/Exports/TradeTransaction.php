<?php
namespace App\Exports;

use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TradeTransaction implements FromCollection, WithHeadings
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
            $data = DB::select("SELECT transaction_id, buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transactions.created_at FROM transactions
                    join users as buy_user on buy_user.id = transactions.buy_user_id
                    join users as sell_user on sell_user.id = transactions.sell_user_id
                    join coins as base_coin_table on base_coin_id = base_coin_table.id
                    join coins as trade_coin_table on trade_coin_id = trade_coin_table.id"
            );
            $data = collect($data);
            if(isset($this->request->from_date) && isset($this->request->to_date)){
                $data = $data->whereBetween('created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
            }
            $data->map(function($data){
               //
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
            __("Transaction Id"), __("Buy User"), __("Sell User"), __("Base Coin"), __("Trade Coin"), __('Price'), __('Amount'), __("Total"), __("Created At")
        ];

    }
}