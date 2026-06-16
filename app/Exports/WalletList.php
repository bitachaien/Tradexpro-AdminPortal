<?php
namespace App\Exports;

use App\User;
use App\Model\Wallet;
use App\Model\WithdrawHistory;
use App\Model\AffiliationHistory;
use App\Model\DepositeTransaction;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class WalletList implements FromCollection, WithHeadings
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
            $data = Wallet::join('users','users.id','=','wallets.user_id')
                ->join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.type'=>PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('wallets.id', 'desc')
                ->select(
                    'wallets.name'
                    ,'users.first_name'
                    ,'users.email'
                    ,'wallets.balance'
                    ,'wallets.created_at'
                    ,'users.last_name'
                    ,'wallets.coin_type'
                );
            if(isset($this->request->from_date) && isset($this->request->to_date)){
                $data = $data->whereBetween('wallets.created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
            }
            $data = $data->get();
            $data->map(function($data){
                $data->first_name = $data->first_name.' '.$data->last_name ;
                $data->balance = $data->balance.' '.$data->coin_type ;
                $data->last_name = '';
                $data->coin_type = '';
            });
            return $data;
        }catch(\Exception $e)  {
            storeException('All user Export', $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            __("Wallet Name"), __("User Name"), __("User Email"), __("Balance"), __("Created At") // __("Coin Type"),
        ];

    }
}