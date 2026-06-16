<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\UserBankService as userBankService;
use Illuminate\Http\Request;

class UserBankController
{
    protected $service ;
    public function __construct(){
        $this->service = new userBankService();
    }

    public function UserbankGet(Request $request){
        $response = $this->service->getUserBank($request->id ?? null);
        return response()->json($response);
    }

    public function UserBankSave(Request $request){
        $response = $this->service->SaveUserBank($request);
        return response()->json($response);
    }

    public function UserBankDelete(Request $request){
        if(isset($request->id))
            $response = $this->service->DeleteUserBank($request->id);
        else
            return response()->json(['success' => false, 'message' =>__('Bank id not found')]);
        return response()->json($response);
    }
}
