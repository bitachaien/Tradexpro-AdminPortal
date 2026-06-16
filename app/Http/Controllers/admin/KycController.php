<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KycList;
use App\Http\Services\KycService;
use App\Http\Repositories\SettingRepository;

class KycController extends Controller
{
    private $countryService;
    private $settingRepo;
    private $kycService;

    public function __construct()
    {
        $this->kycService = new KycService();
        $this->settingRepo = new SettingRepository();
    }
    public function kycList(Request $request)
    {
        $data['tab']='kycSettings';
        if(isset($_GET['tab'])){
            $data['tab']=$_GET['tab'];
        }
        $data['title'] = __('KYC Settings');
        $data['kyc_list'] = $this->kycService->getKycList()['data'];
        $data['kyc_active_list'] = $this->kycService->getKycActiveList()['data'];

        return view('admin.kyc-settings.list', $data);
    }
    public function kycStatusChange(Request $request)
    {
        try{
            $this->kycService->changeStatus($request->kyc_id);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("kycStatusChange",$e->getMessage());
        }
    }
    public function kycWithdrawalSetting(Request $request)
    {
        $data = [
           'kyc_withdrawal_setting_status'=>$request->kyc_withdrawal_setting_status,
           'kyc_withdrawal_setting_list'=>(json_encode($request->kyc_withdrawal_setting_list))
        ];

        if($data['kyc_withdrawal_setting_status']==STATUS_ACTIVE && $data['kyc_withdrawal_setting_list']=='null')
        {
            return redirect()->back()->with(['dismiss' => __('You have to select minimum one option from KYC list')]);
        }

        $response = $this->settingRepo->saveAdminSetting($request->merge($data));
        if($response['success'])
        {
            return redirect()->route('kycList', ['tab' => 'kycWithdrawal'])->with('success', $response['message']);
        }else{
            return redirect()->back()->with(['dismiss' => __('KYC withdrawal not updated')]);
        }
    }
    public function kycTradeSetting(Request $request)
    {
        $data = [
           'kyc_trade_setting_status'=>$request->kyc_trade_setting_status,
           'kyc_trade_setting_list'=>(json_encode($request->kyc_trade_setting_list))
        ];

        if($data['kyc_trade_setting_status']==STATUS_ACTIVE && $data['kyc_trade_setting_list']=='null')
        {
            return redirect()->back()->with(['dismiss' => __('You have to select minimum one option from KYC list')]);
        }

        $response = $this->settingRepo->saveAdminSetting($request->merge($data));
        if($response['success'])
        {
            return redirect()->route('kycList', ['tab' => 'kycTrade'])->with('success', $response['message']);
        }else{
            return redirect()->back()->with(['dismiss' => __('KYC trade not updated')]);
        }
    }

    public function kycStakingSetting(Request $request)
    {
        $data = [
            'kyc_staking_setting_status'=>$request->kyc_staking_setting_status,
            'kyc_staking_setting_list'=>(json_encode($request->kyc_staking_setting_list))
         ];
 
         if($data['kyc_staking_setting_status']==STATUS_ACTIVE && $data['kyc_staking_setting_list']=='null')
         {
             return redirect()->back()->with(['dismiss' => __('You have to select minimum one option from KYC list')]);
         }
 
         $response = $this->settingRepo->saveAdminSetting($request->merge($data));
         if($response['success'])
         {
             return redirect()->route('kycList', ['tab' => 'kycStaking'])->with('success', $response['message']);
         }else{
             return redirect()->back()->with(['dismiss' => __('KYC Staking is not updated')]);
         }
    }
    public function kycUpdateImage($id)
    {
        $data['title'] = __('Upload image');
        $response = $this->kycService->getKycItemDetails(decrypt($id));
        if($response['success'])
        {
            $data['kycDetails'] = $response['data'];
        }
        return view('admin.kyc-settings.update-image',$data);
    }
    public function kycStoreImage(Request $request)
    {
        try{
            $response = $this->kycService->storeKYCItemImage($request);

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("kycStoreImage",$e->getMessage());
        }
        return redirect()->back()->with(['success' => $response['message']]);
    }

    public function kycSettings(Request $request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);
        if($response['success'])
        {
            return redirect()->route('kycList', ['tab' => 'kycSettings'])->with('success',$response['message']);
        }else{
            return redirect()->route('kycList', ['tab' => 'kycSettings'])->with('dismiss',$response['message']);
        }
    }

    public function kycPersonaSettings(Request $request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);
        if($response['success'])
        {
            return redirect()->route('kycList', ['tab' => 'kycPersonaSettings'])->with('success',$response['message']);
        }else{
            return redirect()->route('kycList', ['tab' => 'kycPersonaSettings'])->with('dismiss',$response['message']);
        }
    }
}
