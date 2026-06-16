<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Services\AddonService;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;

class AddonsController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new AddonService();
    }

    public function addonsSettings()
    {
        try {
            $module = Module::allEnabled();
            $data['settings'] = settings();
            if(!empty($module)){

                if(isset($module['IcoLaunchpad']))
                $data['IcoLaunchpad'] = true;

                if(isset($module['BlogNews']))
                $data['BlogNews'] = true;

                if(isset($module['KnowledgeBase']))
                $data['KnowledgeBase'] = true;

                if(isset($module['Pagebuilder']))
                $data['Pagebuilder'] = true;
                
                if(isset($module['P2P']))
                $data['P2P'] = true;

                if(isset($module['DemoTrade']))
                $data['demoTrade'] = true;

            }
        } catch (\Exception $e) {
            storeException('addonsSettings', $e->getMessage());
        }
        return view('admin.addons.settings',$data ?? []);
    }

    public function addonsLists()
    {
        $data = [];
        try {
            $module = Module::allEnabled();
            $setting = allsetting(['launchpad_settings','blog_news_module',
            'knowledgebase_support_module','page_builder_module','p2p_module','demo_trade_module']);
            $data['list'] = [];

            if(!empty($module)){
                // IcoLaunchpad Checked here
                if(isset($module['IcoLaunchpad']) && $setting['launchpad_settings'] ?? false)
                {
                    $data['list'][] = [
                        'title' => 'IcoLaunchpad',
                        'url' => 'dashboardICO',
                    ];
                }
                if(isset($module['BlogNews']) && $setting['blog_news_module'] ?? false)
                {

                    $data['list'][] = [
                        'title' => 'BlogNews',
                        'url' => 'blogDashboard',
                    ];
                }

                if(isset($module['KnowledgeBase']) && $setting['knowledgebase_support_module'] ?? false)
                {

                    $data['list'][] = [
                        'title' => 'KnowledgeBase & Support',
                        'url' => 'knowledgebase_dashboard',
                    ];
                }

                if(isset($module['Pagebuilder']) && $setting['page_builder_module'] ?? false)
                {

                    $data['list'][] = [
                        'title' => 'Page Builder',
                        'url' => 'pageBuilder.LandingList',
                    ];
                }
                
                if(isset($module['P2P']) && $setting['p2p_module'] ?? false)
                {

                    $data['list'][] = [
                        'title' => 'P2P Trade',
                        'url' => 'p2pDashboard',
                    ];
                }
                if(isset($module['DemoTrade']) && $setting['demo_trade_module'] ?? false)
                {

                    $data['list'][] = [
                        'title' => 'Demo Trade',
                        'url' => 'demoCoinList',
                    ];
                }

            }
        } catch (\Exception $e) {
            storeException('addonsLists', $e->getMessage());
        }
        return view('admin.addons.list',$data);
    }


    public function saveAddonsSettings(Request $request)
    {
        try {
            $response = $this->service->saveAddonSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('addonsSettings')->with('success', $response['message']);
            } else {
                return redirect()->route('addonsSettings')->withInput()->with('success', $response['message']);
            }
        } catch(\Exception $e) {
            storeException('adminCookieSettingsSave',$e->getMessage());
            return redirect()->route('addonsSettings')->with(['dismiss' => $e->getMessage()]);
        }
    }
}
