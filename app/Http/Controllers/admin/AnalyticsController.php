<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\AnalyticsService;
use App\Http\Repositories\SettingRepository;

class AnalyticsController extends Controller
{
    private $countryService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
        $this->settingRepo = new SettingRepository();
    }

    public function googleAnalyticsAdd()
    {
        try{
            $data['title'] = __('Google analytics tracking Id update');
            $response = $this->analyticsService->getGoogleTrackingId();
            if($response['data'] !=false)
            {
               $data['data']=$response['data'];
            }
           return view('admin.google-analytics.addEdit',$data);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("googleAnalyticsAdd",$e->getMessage());
        }
    }
    public function googleAnalyticsIDStore(Request $request)
    {
        try{
            $response = $this->analyticsService->storeGoogleTrackingId($request);
            if($response['success'])
            {
                return redirect()->route('googleAnalyticsAdd')->with("success",__('Google tracking Id Updated successfully'));
            }else {
                return redirect()->back()->with("dismiss",__('Google tracking Id not Updated!'));
            }
           
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("googleAnalyticsIDStore",$e->getMessage());
        }
    }
}
