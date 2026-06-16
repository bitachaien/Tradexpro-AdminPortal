<?php

namespace App\Http\Services;

use PHPUnit\Exception;
use App\Model\AdminSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddonService
{
    function __construct()
    {

    }

    public function saveAddonSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        DB::beginTransaction();
        try {
            foreach ($request->except('_token') as $key => $value) {
                if ($request->hasFile($key)) {
                    $image = uploadFile($request->$key, IMG_PATH, isset(allsetting()[$key]) ? allsetting()[$key] : '');
                    AdminSetting::updateOrCreate(['slug' => $key], ['value' => $image]);
                } else {
                    AdminSetting::updateOrCreate(['slug' => $key], ['value' => $value]);
                }
            }

            $response = [
                'success' => true,
                'message' => __('Addons setting updated successfully')
            ];
        } catch (\Exception $e) {
            storeException('saveAddonSetting --> ', $e->getMessage());
            DB::rollBack();
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
            return $response;
        }
        DB::commit();
        return $response;
    }

}
