<?php

namespace App\Http\Repositories;

use App\Model\AdminSetting;
use App\User;
use Illuminate\Support\Facades\Auth;

class User2FARepositorie extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function changeStatus($id)
    {
        try{
            if($id == null) return false;
            $data = $this->model::where("slug","two_factor_list");
            $value = $data->first();
            $array = null;
            if ($value)
                $array = json_decode($value->value,true);
            if($array == null) {
                $array = json_decode("{}",true);
                $array[] = $id;
            }else{
                if(in_array($id,$array)){
                    $key = array_search($id,$array);
                    unset($array[$key]);
                }else{
                    $array[] = $id;
                }
            }
            $array = json_encode($array, JSON_FORCE_OBJECT); // dd($array);
            $this->model::updateOrCreate(
                ["slug" => "two_factor_list"],
                ["value" => $array]
            );

        }catch (\Exception $e) {
            storeException("Update Two Factor Status: ",$e->getMessage());
            return false;
        }
        return true;
    }

    public function saveTwoFactor($request){
        try{
            if(isset($request->two_factor_user))
                $this->model::updateOrCreate(['slug' => "two_factor_user"], ['value' => $request->two_factor_user]);
            if(isset($request->two_factor_admin))
                if($request->two_factor_admin == STATUS_ACTIVE){
                    User::where("id",Auth::id())->update(["email_enabled" => '1']);
                }
                $this->model::updateOrCreate(['slug' => "two_factor_admin"], ['value' => $request->two_factor_admin]);
            if(isset($request->two_factor_withdraw))
                $this->model::updateOrCreate(['slug' => "two_factor_withdraw"], ['value' => $request->two_factor_withdraw]);
            if(isset($request->two_factor_swap))
                $this->model::updateOrCreate(['slug' => "two_factor_swap"], ['value' => $request->two_factor_swap]);
        }catch (\Exception $e){
            storeException("Update Two Factor Data: ",$e->getMessage());
            return false;
        }
        return true;
    }
}
