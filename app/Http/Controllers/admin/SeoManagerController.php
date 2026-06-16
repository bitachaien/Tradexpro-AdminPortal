<?php

namespace App\Http\Controllers\admin;


use App\Http\Requests\Admin\SeoRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\AdminSettingService;

class SeoManagerController extends Controller
{
    public $settingService;
    public function __construct()
    {
        $this->settingService = new AdminSettingService();
    }

    public function seoManagerAdd()
    {
        try{
            $data['title'] = __('Update SEO Configuration');
            $data['seo_image'] = allsetting('seo_image');
            $data['seo_meta_keywords'] = allsetting('seo_meta_keywords');
            $data['seo_meta_description'] = allsetting('seo_meta_description');
            $data['seo_social_title'] = allsetting('seo_social_title');
            $data['seo_social_description'] = allsetting('seo_social_description');
            return view('admin.seo-manager.addEdit',$data);
        }catch (\Exception $e) {
            storeException("seoManagerAdd",$e->getMessage());
        }
    }
    public function seoManagerUpdate(SeoRequest $request)
    {
        try{

            $data =
            [
                'seo_meta_keywords'=>$request->meta_keywords,
                'seo_meta_description'=>$request->meta_description,
                'seo_social_title'=>$request->social_title,
                'seo_social_description'=>$request->social_description,
            ];
            if (!empty($request->image)) {
                $old_img = allsetting('seo_image');
                $imageName = uploadFile($request->image,IMG_PATH,$old_img);
                $data['seo_image'] = $imageName;
            }
            $response = $this->settingService->generalSetting($data);
        }catch (\Exception $e) {
            storeException("seoManagerUpdate",$e->getMessage());
        }
        return redirect()->route('seoManagerAdd')->with(['success'=> $response['message']]);
    }
}
