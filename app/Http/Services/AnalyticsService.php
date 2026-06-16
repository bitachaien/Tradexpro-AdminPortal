<?php
namespace App\Http\Services;


use App\Http\Repositories\SettingRepository;
use App\Model\AdminSetting;

class AnalyticsService extends BaseService
{
    public $model = AdminSetting::class;
    public $repository = SettingRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getGoogleTrackingId()
    {
        try{
            $data = allsetting('google_analytics_tracking_id');
           
            $response = ['success' => true, 'message' => __('Google analytics tracking Id'), 'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getGoogleTrackingId",$e->getMessage());
        }
        return $response;
    }

    public function storeGoogleTrackingId($request)
    {
        try{
            $response = $this->object->saveAdminSetting($request);
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getGoogleTrackingId",$e->getMessage());
        }
        return $response;
    }
}