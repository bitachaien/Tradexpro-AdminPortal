<?php

namespace App\Http\Repositories;


use App\Model\Bank;

class BankRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getBanksData()
    {
        return Bank::get();
    }

    public function saveBank($data)
    {
        if(isset($data['id']))
        {   
            return Bank::where('id',$data['id'])->update($data);
        } else {
            return Bank::Create($data);
        }
    }

    public function statusChange($data)
    {
        $bank = Bank::where('id',$data['bank_id'])->first();

        if ($bank) {
            if ($bank->status == 1) {
               $bank->update(['status' => 0]);
            } else {
                $bank->update(['status' => 1]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function deleteBank($data)
    {
        $bank = Bank::where('id',$data['bank_id'])->first();

        if ($bank) {
            $bank->delete();
            return true;
        } else {
            return false;
        }
    }

    public function getBank($data)
    {
        $bank = Bank::where('id',$data['bank_id'])->first();
        if ($bank) {

            return $bank;

        } else {

            return null;
        }
    }
}
