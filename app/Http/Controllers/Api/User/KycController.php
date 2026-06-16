<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\KycService;

class KycController extends Controller
{
    private $serviceKYC;

    function __construct()
    {
        $this->serviceKYC = new KycService();
    }

    public function kycActiveList()
    { 
        try{
           $response = $this->serviceKYC->getKycActiveList();
           if($response['success'])
           {
            $data = $response['data'];
            $data->map(function($query){
                if(!empty($query->image))
                {
                    $query->image=imageSrc($query->image,IMG_PATH);
                }else{
                    $query->image=asset('assets/admin/images/nid.svg');;
                }
            });
            $response['data']=$data;
           }
        } catch (\Exception $e) {
            storeException("getKYCActiveList",$e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }
}
