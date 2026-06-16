<?php
namespace App\Exports;

use App\User;
use App\Model\WithdrawHistory;
use App\Model\AffiliationHistory;
use App\Model\DepositeTransaction;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class Users implements FromCollection, WithHeadings
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
            $data = User::whereRole(USER_ROLE_USER)->get(['first_name','email','role','status','created_at']);
            if(isset($this->request->from_date) && isset($this->request->to_date)){
                $data = $data->whereBetween('created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
            }
            $data->map(function($data){
                $data->role = userRole($data->role);
                $data->status = userStatusActionExport($data->status);
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
            __("User Name"), __("Email ID"), __("Role"), __("Status"), __("Created At")
        ];

    }
}