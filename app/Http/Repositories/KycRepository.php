<?php

namespace App\Http\Repositories;


use App\Model\KycList;

class KycRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getKycList()
    {
        try{
           return $data = KycList::latest()->get();

        }catch (\Exception $e) {
            storeException("getKycList",$e->getMessage());
        }
        return $response;
    }
    public function getKycActiveList()
    {
        try{
           return $data = KycList::where('status',STATUS_ACTIVE)->latest()->get();

        }catch (\Exception $e) {
            storeException("getKycList",$e->getMessage());
        }
        return $response;
    }
    public function changeStatus($id)
    {
        try{
            $data = $this->model::find($id);
            if ($data) {
                if ($data->status == 1) {
                   $data->update(['status' => 0]);
                } else {
                    $data->update(['status' => 1]);
                }
                return true;
            } else {
                return false;
            }

         }catch (\Exception $e) {
             storeException("getKycList",$e->getMessage());
         }
         return true;
    }
    public function getKycItemDetails($id)
    {
        try{
            $data = $this->model::find($id);

         }catch (\Exception $e) {
             storeException("getKycList",$e->getMessage());
         }
         return $data;
    }
    public function storeKYCItemImage($id, $data)
    {
        try{
            $kyc = KycList::find($id);
            if(isset($data["name"]))
                $kyc->name = $data["name"];
            if(isset($data["photo"]))
                $kyc->image = $data["photo"];
            $kyc->save();
        }catch (\Exception $e) {
            storeException("getKycList",$e->getMessage());
        }
        return true;
}
}
