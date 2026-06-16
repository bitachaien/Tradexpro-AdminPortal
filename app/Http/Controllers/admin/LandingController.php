<?php

namespace App\Http\Controllers\admin;

use App\Http\Repositories\SettingRepository;
use App\Http\Services\LandingService;
use App\Http\Services\Logger;
use App\Model\AdminSetting;
use App\Model\CustomPage;
use App\Model\LandingFeature;
use App\Model\SocialMedia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public $settingRepo;
    public $landingService;
    public function __construct()
    {
        $this->settingRepo = new SettingRepository();
        $this->landingService = new LandingService();
    }

    // custom page list
    public function adminCustomPageList(Request $request)
    {
        $data['title'] = __("Custom Page List");
        if ($request->ajax()) {
            $cp = CustomPage::select('*')->orderBy('data_order','ASC');
            $user_url = settings('exchange_url');
            return datatables($cp)
                ->addColumn('key', function ($item) use($user_url) {
                    return '<span onclick="navigator.clipboard.writeText(this.innerHTML) ; alert(\''.__("Url copyed").'\')">' . $user_url. 'page-details/' .$item->key . '</span>';
                })
                ->addColumn('type', function ($item) {
                    return custom_page_type($item->type);
                })
                ->addColumn('actions', function ($item) {
                    $html = '<input type="hidden" value="'.$item->id.'" class="shortable_data">';
                    $html .= '<ul class="d-flex activity-menu justify-content-center align-items-center">';

                    $html .= ' <li class="viewuser"><a title="Edit" href="' . route('adminCustomPageEdit', $item->id) . '"><i class="fa fa-pencil" style="font-size: 16px"></i></a> <span></span></li>';
                    $html .= delete_html('adminCustomPageDelete',encrypt($item->id));
                    $html .=' </ul>';
                    return $html;
                })
                ->rawColumns(['actions','key'])->make(true);
        }

        return view('admin.custom-page.custom-pages-list', $data);
    }

    // custom page add
    public function adminCustomPageAdd()
    {
        $data['title'] = __("Add Page");
        return view('admin.custom-page.custom-pages', $data);
    }

    // edit the custom page
    public function adminCustomPageEdit($id)
    {
        $data['title'] = __("Update Page");
        $data['cp'] = CustomPage::findOrFail($id);

        return view('admin.custom-page.custom-pages', $data);
    }

    // custom page save setting
    public function adminCustomPageSave(Request $request)
    {
        try {
            $rules = [
                'title' => 'required|max:255',
                'key' => 'required|max:255',
                'type' => 'required|integer',
                'page_type' => 'required|integer'
            ];
            if($request->page_type == CUSTOM_PAGE_LINK_URL) {
                $rules['page_link'] = 'required';
            } else {
                $rules['body'] = 'required';
            }
            $messages = [
                'title.required' => __('Title Can\'t be empty!'),
                'type.required' => __('Please select a type'),
                'key.required' => __('Slug Can\'t be empty!'),
                'body.required' => __('Description Can\'t be empty!'),
                'page_link.required' => __('Page url is required'),
                'page_type.required' => __('Page type is required')
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors[0];

                return redirect()->back()->withInput()->with(['dismiss' => $data['message']]);
            }
            $check = $this->landingService->checkKeyCustom($request->key,$request->edit_id);
            if(!$check){
                return redirect()->back()->withInput()->with(['dismiss' => 'Duplicate Key found!']);
            }
            $custom_page = [
                'title' => $request->title
                , 'key' => $request->key
                , 'type' => $request->type
                , 'page_type' => $request->page_type
                , 'status' => STATUS_SUCCESS
            ];
            if ($request->page_type == CUSTOM_PAGE_LINK_URL) {
                $custom_page['description'] = NULL;
                $custom_page['page_link'] = $request->page_link;
            } else {
                $custom_page['page_link'] = NULL;
                $custom_page['description'] = $request->body;
            }
            CustomPage::updateOrCreate(['id' => $request->edit_id], $custom_page);

            if ($request->edit_id) {
                $message = __('Custom page updated successfully');
            } else {
                $message = __('Custom Page created successfully');
            }

            return redirect()->route('adminCustomPageList')->with(['success' => $message]);
        } catch (\Exception $e) {
            storeException('adminCustomPageSave', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    // delete custom page
    public function adminCustomPageDelete($id)
    {

        if (isset($id)) {
            CustomPage::where(['id' => decrypt($id)])->delete();
        }

        return redirect()->back()->with(['success' => __('Deleted Successfully')]);
    }


    // change custom page order
    public function customPageOrder(Request $request)
    {
        $vals = explode(',',$request->vals);
        foreach ($vals as $key => $item){
            CustomPage::where('id',$item)->update(['data_order'=>$key]);
        }

        return response()->json(['message'=>__('Page ordered change successfully')]);
    }

    // landing tab
    public function landingTab($request)
    {
        $tab = 'hero';
        if(isset($request->customization_title)) {
            $tab = 'contact';
        } elseif (isset($request->landing_title)) {
            $tab = 'hero';
        } elseif (isset($request->market_trend_title)) {
            $tab = 'features';
        }elseif (isset($request->apple_store_link)) {
            $tab = 'links';
        }
        return $tab;
    }

//    Landing Settings
    public function adminLandingSetting(Request $request)
    {
        if (isset($_GET['tab'])) {
            $data['tab']=$_GET['tab'];
        } else {
            $data['tab']='hero';
        }
        $data['adm_setting'] = allsetting();

        return view('admin.settings.landing-settings',$data);
    }

    // save cms setting
    public function adminLandingSettingSave(Request $request)
    {
        $rules = [];
        foreach ($request->all() as $key => $item) {
            if ($request->hasFile($key)) {
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules[$key] = "image|mimes:jpg,jpeg,png,gif|max:$file_size";
            }
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = [];
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
            $data['message'] = $errors;
            return redirect()->route('adminLandingSetting',['tab' => $this->landingTab($request)])->with(['dismiss' => $errors[0]]);
        }
//        $key_data = ['landing_title','landing_description','landing_feature_title','landing_banner_image'];
        foreach ($request->all() as $key => $item) {
//            if (in_array($key,$key_data)) {
            if (!empty($request->$key)){
                $setting = AdminSetting::where('slug', $key)->first();
                if (empty($setting)) {
                    $setting = new AdminSetting();
                    $setting->slug = $key;
                }
                if ($request->hasFile($key)) {
                    $setting->value = uploadFile($request->$key, IMG_PATH, isset(allsetting()[$key]) ? allsetting()[$key] : '');
                } else {
                    $setting->value = $request->$key;
                }
                $setting->save();
            }
        }

        return redirect()->route('adminLandingSetting',['tab' => $this->landingTab($request)])->with(['success' => __('Landing Page Setting Successfully Updated!')]);

    }

    //  List
    public function adminFeatureList(Request $request)
    {
        $data['title'] = __('Landing Feature');
        if ($request->ajax()) {
            $data['items'] = LandingFeature::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu justify-content-center align-items-center">
                        <li class="viewuser"><a href="' . route('adminFeatureEdit', $item->id) . '"><i class="fa fa-pencil" style="font-size: 16px"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('adminFeatureDelete', $item->id) . '"><i class="fa fa-trash" style="font-size: 16px"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status'])
                ->make(true);
        }

        return view('admin.landing_feature.list', $data);
    }

    // View Add new
    public function adminFeatureAdd()
    {
        $data['title']=__('Add Landing Feature');
        $data['button_title']=__('Add Feature');
        return view('admin.landing_feature.addEdit',$data);
    }

    // Create New
    public function adminFeatureSave(Request $request)
    {

        try {
            $rules = [
                'feature_title'=>'required',
                'description'=>'required',
                'status'=>'required',
            ];
            if(!empty($request->feature_icon)){
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules['feature_icon']="image|mimes:jpg,jpeg,png,PNG,JPG,JPEG|max:$file_size";
            }
            $messages = [
                'feature_title.required' => __('Title field can not be empty'),
                'description.required' => __('Details field can not be empty'),
                'status.required' => __('Status field can not be empty'),
                'feature_icon.image' => __('Feature icon should be an image'),
                'feature_icon.mimes' => __('Feature icon should be jpg or png type image'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                return redirect()->back()->withInput()->with(['dismiss' => $errors[0]]);
            }

            $response = $this->landingService->saveLandingFeature($request);
            if ($response['success'] == true) {
                return redirect()->route('adminFeatureList')->with(['success'=> $response['message']]);
            } else {
                return redirect()->back()->with(['dismiss'=> $response['message']]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // Edit
    public function adminFeatureEdit($id)
    {
        $data['title'] = __('Update Landing Feature');
        $data['button_title'] = __('Update Feature');
        $data['item'] = LandingFeature::findOrFail($id);
        if (isset($data['item'])) {
            return view('admin.landing_feature.addEdit',$data);
        } else {
            return redirect()->back()->with('dismiss', __('Data not found'));
        }
    }

    // Delete
    public function adminFeatureDelete($id)
    {

        if(isset($id)) {
            LandingFeature::where(['id'=>$id])->delete();
        }
        return redirect()->back()->with(['success'=>__('Deleted Successfully!')]);
    }
    //  List
    public function adminSocialMediaList(Request $request)
    {
        $data['title'] = __('Social Media');
        if ($request->ajax()) {
            $data['items'] = SocialMedia::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu justify-content-center align-items-center">
                        <li class="viewuser"><a href="' . route('adminSocialMediaEdit', $item->id) . '"><i class="fa fa-pencil" style="font-size: 16px"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('adminSocialMediaDelete', $item->id) . '"><i class="fa fa-trash" style="font-size: 16px"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status'])
                ->make(true);
        }

        return view('admin.social_media.list', $data);
    }

    // View Add new
    public function adminSocialMediaAdd()
    {
        $data['title']=__('Add Social Media');
        $data['button_title']=__('Add Media');
        return view('admin.social_media.addEdit',$data);
    }

    // Create New
    public function adminSocialMediaSave(Request $request)
    {

        try {
            $rules = [
                'media_title'=>'required',
                'media_link'=>'required',
                'status'=>'required',
            ];
            if(!empty($request->feature_icon)){
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules['feature_icon']="image|mimes:jpg,jpeg,png,PNG,JPG,JPEG|max:$file_size";
            }
            $messages = [
                'media_title.required' => __('Title field can not be empty'),
                'media_link.required' => __('Link field can not be empty'),
                'status.required' => __('Status field can not be empty'),
                'media_icon.image' => __('Media icon should be an image'),
                'media_icon.mimes' => __('Media icon should be jpg or png type image'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                return redirect()->back()->withInput()->with(['dismiss' => $errors[0]]);
            }

            $response = $this->landingService->saveLandingSocialMedia($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSocialMediaList')->with(['success'=> $response['message']]);
            } else {
                return redirect()->back()->with(['dismiss'=> $response['message']]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // Edit
    public function adminSocialMediaEdit($id)
    {
        $data['title'] = __('Update Social Media');
        $data['button_title'] = __('Update Media');
        $data['item'] = SocialMedia::findOrFail($id);
        if (isset($data['item'])) {
            return view('admin.social_media.addEdit',$data);
        } else {
            return redirect()->back()->with('dismiss', __('Data not found'));
        }
    }

    // Delete
    public function adminSocialMediaDelete($id)
    {

        if(isset($id)) {
            SocialMedia::where(['id'=>$id])->delete();
        }
        return redirect()->back()->with(['success'=>__('Deleted Successfully!')]);
    }

    public function customPageSlugCheck(Request $request){
        return $this->landingService->customPageSlugCheck($request->except('_token'));
    }

    public function adminLandingApiLinkSave(Request $request)
    {
        $response = $this->landingService->adminLandingApiLinkSave($request->except('_token'));
        if ($response['success']) {
            return redirect()->route('adminLandingSetting',['tab' => 'links'])->with(['success' => $response['message']]);
        } else {
            return redirect()->route('adminLandingSetting',['tab' => 'links'])->with(['dismiss'=> $response['message']]);
        }
    }

    public function adminLandingPairAssetSave(Request $request)
    {
        $response = $this->landingService->adminLandingPairAssetSave($request->except('_token'));
        if ($response['success']) {
            return redirect()->route('adminLandingSetting',['tab' => 'pair_assets'])->with(['success' => $response['message']]);
        } else {
            return redirect()->route('adminLandingSetting',['tab' => 'pair_assets'])->with(['dismiss'=> $response['message']]);
        }
    }

    public function adminLandingSectionSettingsSave(Request $request)
    {
        $response = $this->landingService->adminLandingSectionSettingsSave($request->except('_token'));
        if ($response['success']) {
            return redirect()->route('adminLandingSetting',['tab' => 'section_settings'])->with(['success' => $response['message']]);
        } else {
            return redirect()->route('adminLandingSetting',['tab' => 'section_settings'])->with(['dismiss'=> $response['message']]);
        }
    }
}
