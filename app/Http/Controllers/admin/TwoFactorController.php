<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Services\User2FAService;
use App\Model\LangName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session as SessionLocal;
use MongoDB\Driver\Session;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->TwoFactorService = new User2FAService();
    }
    public function index (){
        $data['tab']='two_factor_list';
        if(SessionLocal::has("tab")){
            $data['tab']=SessionLocal::get("tab");
            SessionLocal::remove("tab");
        }
        $data["title"] = __("Two Factor Settings");
        $data['settings'] = allsetting();
        $data['languages'] = LangName::where(['status' => STATUS_ACTIVE])->get();
        $twofa_list = settings("two_factor_list");
        $data["twofa_list"] = json_decode($twofa_list,true);

        return view("admin.two-factor.two-factor",$data);
    }

    public function saveTwoFactorList(Request $request){
        try{
            if($this->TwoFactorService->changeStatus($request->id))
                return response()->json(["success" => true, "message" => __("Status updated successfully")]);
            return response()->json(["success" => true, "message" => __("Status updated failed")]);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("kycStatusChange",$e->getMessage());
            return response()->json($response);
        }
    }

    public function saveTwoFactorData(Request $request){
        try{
            $response = $this->TwoFactorService->saveTwoFactor($request);
            if($response["success"])
                return redirect()->back()->with(["success" => $response["message"], "tab" => $request->tab ?? "two_factor_list"]);
            return redirect()->back()->with(["dismiss" => $response["message"], "tab" => $request->tab ?? "two_factor_list"]);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("kycStatusChange",$e->getMessage());
            return redirect()->back()->with(["dismiss" => $response["message"], "tab" => $request->tab ?? "two_factor_list"]);
        }
    }


}
