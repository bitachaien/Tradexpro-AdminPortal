<?php

namespace App\Http\Controllers\admin;

use App\Model\UserNavbar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\AdminSettingService;
use App\Http\Requests\Admin\NavbarUpdateRequest;

class ThemeSettingsController extends Controller
{
    private $adminSettingService;
    public function __construct()
    {
        $this->adminSettingService = new AdminSettingService();
    }

    public function addEditThemeSettingsStore(Request $request)
    {
        try{
            $response = $this->adminSettingService->saveThemeColorSettings($request);
        } catch(\Exception $e) {
            storeException('addEditThemeSettingsStore', $e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }
        return back()->with(['success' => $response['message']]);
    }

    public function resetThemeColorSettings()
    {
        try{
            $response = $this->adminSettingService->resetThemeColorSettings();
        } catch(\Exception $e) {
            storeException('addEditThemeSettingsStore', $e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }
        return back()->with(['success' => $response['message']]);
    }

    public function themeNavebarSettings()
    {
        try{
            $data['title'] = __("Theme Navber Setting");
            $data['navbar'] = UserNavbar::get();
            return view('admin.settings.theme_navbar.navbar', $data);
        } catch(\Exception $e) {
            storeException('themeNavebarSettings', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __("Something went wrong")]);
        }
    }

    public function themeNavebarSettingsSave(NavbarUpdateRequest $request)
    {
        try{
            $response = $this->adminSettingService->themeNavebarSettingsSave($request);
            return response()->json($response);
        } catch(\Exception $e) {
            storeException('themeNavebarSettingsSave', $e->getMessage());
            return response()->json(responseData(false, __('Something went wrong!')));
        }
    }

    public function themesSettingsPage(Request $request)
    {
        try{
            $response = $this->adminSettingService->themeNavebarSettingsSave($request);
            $data['tab'] = 'theme_color';
            $data['settings'] = allsetting();
            $data['themeColors'] = themeColorsFromDb();
            $data['navbar'] = UserNavbar::get();
            if (session()->has('tab')) {
                $data['tab'] = session()->get('tab');
            }
            return view('admin.settings.theme.themes',$data);
        } catch(\Exception $e) {
            storeException('themeSettingsPage', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __("Something went wrong")]);
        }
    }

    public function themesSettingSave(Request $request)
    {
        try{
            $response = $this->adminSettingService->saveThemeSettings($request);
        } catch (\Exception $e) {
            storeException('addEditThemeSettingsStore', $e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);//'tab' => 'footer_custom_pages',
        }
        return back()->with(['success' => $response['message']]);
    }

    public function saveThemeColors(Request $request)
    {
        try {
            $response = $this->adminSettingService->saveThemeColors($request);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->back()->withInput()->with('success', $response['message']);
            }
        } catch(\Exception $e) {
            storeException('saveThemeColors',$e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }
    }
}
