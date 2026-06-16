<?php

namespace App\Http\Services;

use App\Model\UserBank;
use Illuminate\Support\Facades\DB;

class UserBankService
{
   public function __construct(){}

   public function getUserBank ($bank_id = null)
   {
       try{
           $id = auth()->id() ?? auth()->guard('api')->id();
           if($bank_id){
               $bank = UserBank::where(['id' => $bank_id,'user_id'=>$id,'status' => STATUS_ACTIVE])->first();
               if($bank)
                   return ['success' => true, 'data' => $bank, 'message' => __("User bank get successfully")];
               else
                   return ['success' => false, 'data' => (Object)[5], 'message' => __("User bank get successfully")];
           }
           $bank = UserBank::where('user_id',$id)->whereStatus(STATUS_ACTIVE)->get();
           if($bank)
               return ['success' => true, 'data' => $bank, 'message' => __("User bank get successfully")];
           else
               return ['success' => false, 'data' => (Object)[], 'message' => __("User bank get successfully")];
       }catch (\Exception $e){
           storeException('UserBank get :',$e->getMessage());
           return ['success' => false, 'data' => (Object)[], 'message' => __('User bank get failed !!')];
       }
   }


   public function SaveUserBank($request){
       $id = auth()->id() ?? auth()->guard('api')->id();
       $message = isset($request->id) ? __('Bank update successfully') : __('Bank created successfully');
       $failmessage = isset($request->id) ? __('Bank update failed !!') : __('Bank create failed !!');
       DB::beginTransaction();
        try{
            $request->merge(['user_id' => $id]);
            $find = isset($request->id) ? [ 'id' => $request->id ] : [ 'id' => 0 ];
            $save = UserBank::updateOrCreate($find,$request->except(['_token']));
        }catch (\Exception $e){
          DB::rollBack();
          storeException('UserBank :',$e->getMessage());
          return ['success' => false, 'message' => $failmessage];
        }
        DB::commit();
       return ['success' => true, 'message' => $message];
   }


   public function DeleteUserBank($id)
   {
       DB::beginTransaction();
       try{
           if($data = UserBank::find($id)) {
               if($data->user_id == auth()->id() ?? auth()->guard('api')->id())
                   $update = UserBank::findOrFail($id)->update(['status' => STATUS_DELETED]);
               else
                   return ['success' => false, 'message' => __('Bank not found !!')];
           }else {
               return ['success' => false, 'message' => __('Bank not found !!')];
           }
       }catch (\Exception $e){
           DB::rollBack();
           storeException('UserBank delete :',$e->getMessage());
           return ['success' => false, 'message' => __('Bank delete failed !!')];
       }
       DB::commit();
       return ['success' => true, 'message' => __('Bank deleted successfully !!')];
   }
}
