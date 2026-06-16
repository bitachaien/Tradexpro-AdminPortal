<?php

namespace App\Http\Services;


use App\Model\Coin;
use App\Model\CoinPair;
use App\Model\CurrencyList;
use App\Model\CustomPage;
use App\Model\UserNavbar;
use App\Model\SocialMedia;
use App\Model\AdminSetting;
use App\Model\Announcement;
use App\Model\LandingBanner;
use App\Model\LandingFeature;
use App\Http\Repositories\AdminSettingRepository;
use App\Model\Wallet;


class LandingService
{
    public $logger;
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /*
     * save or update landing feature
     *
     */
    public function saveLandingFeature($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'feature_title'=> $request->feature_title,
                'feature_url'=> $request->feature_url ?? "",
                'description'=> $request->description,
                'status'=> $request->status
            ];
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = LandingFeature::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->feature_icon))) {
                    $old_img = $item->feature_icon;
                }
            }
            if (!empty($request->feature_icon)) {
                $icon = uploadFile($request->feature_icon,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['feature_icon'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                LandingFeature::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Landing feature updated successfully!')];
            } else {
                LandingFeature::create($data);
                $response = ['success' => true, 'message' => __('Landing feature created successfully!')];
            }
        } catch (\Exception $e) {
            storeException('saveLandingFeature', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    /*
     *
     * create or update social media
     */
    public function saveLandingSocialMedia($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'media_title'=> $request->media_title,
                'media_link'=> $request->media_link,
                'status'=> $request->status
            ];
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = SocialMedia::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->media_icon))) {
                    $old_img = $item->media_icon;
                }
            }
            if (!empty($request->media_icon)) {
                $icon = uploadFile($request->media_icon,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['media_icon'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                SocialMedia::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Social media updated successfully!')];
            } else {
                SocialMedia::create($data);
                $response = ['success' => true, 'message' => __('Social media created successfully!')];
            }
        } catch (\Exception $e) {
            storeException('saveLandingSocialMedia', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    public function customPageSlugCheck($post_data)
    {
        $response['slug'] = make_unique_slug($post_data['title'], 'custom_pages', 'key');
        return response()->json($response);
    }

    public function checkKeyCustom($key,$id = null){
        if(isset($id)){
            $res = CustomPage::where('id','<>',$id)->where('key', 'like', '%'.$key.'%')->get()->count();
        }else{
            $res = CustomPage::where('key', 'like', '%'.$key.'%')->get()->count();
        }
        if($res){
            return false;
        }else{
            return true;
        }
    }

    //
    public function adminLandingApiLinkSave($request)
    {
        try {
            AdminSetting::updateOrCreate(['slug' => 'apple_store_link'],['value' => $request['apple_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'android_store_link'],['value' => $request['android_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'google_store_link'],['value' => $request['google_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'macos_store_link'],['value' => $request['macos_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'windows_store_link'],['value' => $request['windows_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'linux_store_link'],['value' => $request['linux_store_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'api_link'],['value' => $request['api_link'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'download_link_display_type'],['value' => $request['download_link_display_type'] ?? SHOW_LINK ]);
            AdminSetting::updateOrCreate(['slug' => 'download_link_title'],['value' => $request['download_link_title'] ?? '']);
            AdminSetting::updateOrCreate(['slug' => 'download_link_description'],['value' => $request['download_link_description'] ?? '']);

            $response = responseData(true,__('Settings updated successfully'));
        } catch (\Exception $e) {
            storeException('adminLandingApiLinkSave', $e->getMessage());
            $response = responseData(false,__('Something went wrong'));
        }
        return $response;
    }

    public function adminLandingSectionSettingsSave($request)
    {
        try {
            AdminSetting::updateOrCreate(['slug' => 'landing_first_section_status'],['value' => $request['landing_first_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_second_section_status'],['value' => $request['landing_second_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_third_section_status'],['value' => $request['landing_third_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_fourth_section_status'],['value' => $request['landing_fourth_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_fifth_section_status'],['value' => $request['landing_fifth_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_sixth_section_status'],['value' => $request['landing_sixth_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_seventh_section_status'],['value' => $request['landing_seventh_section_status']]);
            AdminSetting::updateOrCreate(['slug' => 'landing_advertisement_section_status'],['value' => $request['landing_advertisement_section_status']]);

            $response = responseData(true,__('Settings updated successfully'));
        } catch (\Exception $e) {
            storeException('adminLandingSectionSettingsSave', $e->getMessage());
            $response = responseData(false,__('Something went wrong'));
        }
        return $response;
    }
    //
    public function adminLandingPairAssetSave($request)
    {
        try {
            AdminSetting::updateOrCreate(['slug' => 'pair_assets_list'],['value' => $request['pair_assets_list']]);
            AdminSetting::updateOrCreate(['slug' => 'pair_assets_base_coin'],['value' => $request['pair_assets_base_coin']]);
            $response = responseData(true,__('Settings updated successfully'));
        } catch (\Exception $e) {
            storeException('adminLandingPairAssetSave', $e->getMessage());
            $response = responseData(false,__('Something went wrong'));
        }
        return $response;
    }

    // user coller list
    public function userEndColorList()
    {
        $themeColor = allsetting("default_theme_color") ?? 'green' ;
        $settings = themeColorsFromDb();
        $response = [];
        $seletedTheme = "_" . $themeColor;
        $data['--primary-color'] = $settings["user_primary_color$seletedTheme"] ?? "" ;
        $data['--text-primary-color'] = $settings["user_text_primary_color$seletedTheme"] ?? "" ;
        $data['--text-primary-color-2'] = $settings["user_text_primary_color_2$seletedTheme"] ?? "" ;
        $data['--text-primary-color-3'] = $settings["user_text_primary_color_3$seletedTheme"] ?? "" ;
        $data['--text-primary-color-4'] = $settings["user_text_primary_color_4$seletedTheme"] ?? "" ;
        $data['--border-color'] = $settings["user_border_color$seletedTheme"] ?? "" ;
        $data['--border-color-1'] = $settings["user_border_color_1$seletedTheme"] ?? "" ;
        $data['--border-color-2'] = $settings["user_border_color_2$seletedTheme"] ?? "" ;
        $data['--hover-color'] = $settings["user_hover_color$seletedTheme"] ?? "" ;
        $data['--font-color'] = $settings["user_font_color$seletedTheme"] ?? "" ;
        $data['--bColor'] = $settings["user_bColor$seletedTheme"] ?? "" ;
        $data['--title-color'] = $settings["user_title_color$seletedTheme"] ?? "" ;
        $data['--white'] = $settings["user_white$seletedTheme"] ?? "" ;
        $data['--black'] = $settings["user_black$seletedTheme"] ?? "" ;
        $data['--color-pallet-1'] = $settings["user_color_pallet_1$seletedTheme"] ?? "" ;
        $data['--background-color'] = $settings["user_background_color$seletedTheme"] ?? "" ;
        $data['--background-color-trade'] = $settings["user_background_color_trade$seletedTheme"] ?? "" ;
        $data['--main-background-color'] = $settings["user_main_background_color$seletedTheme"] ?? "" ;
        $data['--card-background-color'] = $settings["user_card_background_color$seletedTheme"] ?? "" ;
        $data['--table-background-color'] = $settings["user_table_background_color$seletedTheme"] ?? "" ;
        $data['--footer-background-color'] = $settings["user_footer_background_color$seletedTheme"] ?? "" ;
        $data['--background-color-hover'] = $settings["user_background_color_hover$seletedTheme"] ?? "" ;
        $data['--authentication-page-text-color'] = $settings["authentication_page_text_color$seletedTheme"] ?? "" ;
        $data['--primary-background-color'] = $settings["user_primary_background_color$seletedTheme"] ?? "" ;

        foreach ($data as $key => $val) {
            $response[]=[
                'name' => $key,
                'value' => $val,
            ];
        }
        return $response;
    }

    public function userEndDarkColorList()
    {
        $themeColor = allsetting("default_theme_color") ?? 'green' ;
        $settings = themeColorsFromDb();
        $response = [];
        $seletedTheme = "_" . $themeColor;
        $data['--primary-color'] = $settings["user_dark_primary_color$seletedTheme"] ?? "" ;
        $data['--text-primary-color'] = $settings["user_dark_text_primary_color$seletedTheme"] ?? "" ;
        $data['--text-primary-color-2'] = $settings["user_dark_text_primary_color_2$seletedTheme"] ?? "" ;
        $data['--text-primary-color-3'] = $settings["user_dark_text_primary_color_3$seletedTheme"] ?? "" ;
        $data['--text-primary-color-4'] = $settings["user_dark_text_primary_color_4$seletedTheme"] ?? "" ;
        $data['--border-color'] = $settings["user_dark_border_color$seletedTheme"] ?? "" ;
        $data['--border-color-1'] = $settings["user_dark_border_color_1$seletedTheme"] ?? "" ;
        $data['--border-color-2'] = $settings["user_dark_border_color_2$seletedTheme"] ?? "" ;
        $data['--hover-color'] = $settings["user_dark_hover_color$seletedTheme"] ?? "" ;
        $data['--font-color'] = $settings["user_dark_font_color$seletedTheme"] ?? "" ;
        $data['--bColor'] = $settings["user_dark_bColor$seletedTheme"] ?? "" ;
        $data['--title-color'] = $settings["user_dark_title_color$seletedTheme"] ?? "" ;
        $data['--white'] = $settings["user_dark_white$seletedTheme"] ?? "" ;
        $data['--black'] = $settings["user_dark_black$seletedTheme"] ?? "" ;
        $data['--color-pallet-1'] = $settings["user_dark_color_pallet_1$seletedTheme"] ?? "" ;
        $data['--background-color'] = $settings["user_dark_background_color$seletedTheme"] ?? "" ;
        $data['--background-color-trade'] = $settings["user_dark_background_color_trade$seletedTheme"] ?? "" ;
        $data['--main-background-color'] = $settings["user_dark_main_background_color$seletedTheme"] ?? "" ;
        $data['--card-background-color'] = $settings["user_dark_card_background_color$seletedTheme"] ?? "" ;
        $data['--table-background-color'] = $settings["user_dark_table_background_color$seletedTheme"] ?? "" ;
        $data['--footer-background-color'] = $settings["user_dark_footer_background_color$seletedTheme"] ?? "" ;
        $data['--background-color-hover'] = $settings["user_dark_background_color_hover$seletedTheme"] ?? "" ;
        $data['--authentication-page-text-color'] = $settings["authentication_dark_page_text_color$seletedTheme"] ?? "" ;
        $data['--primary-background-color'] = $settings["user_dark_primary_background_color$seletedTheme"] ?? "" ;

        foreach ($data as $key => $val) {
            $response[]=[
                'name' => $key,
                'value' => $val,
            ];
        }
        return $response;
    }

    public function getUserNavbar(){
        $navber = UserNavbar::get();
        $data = [];
        foreach($navber as $nav){
            if($nav->main_id == NULL){
                $data[$nav->slug] = [
                    'name' => $nav->title,
                    'status' => $nav->status == true
                ];
                if ($nav->sub) {
                    foreach ($navber as $sub) {
                        if($nav->id == $sub->main_id){
                            $data[$nav->slug][$sub->slug] = [
                                'name' => $sub->title,
                                'status' => $sub->status == true
                            ];
                        }
                    }
                }
            }
        }
        return $data;

    }

    public function getMarketOverviewCoinStatisticListWebsocketData()
    {
        $data['highlight_coin'] = null;
        $data['new_listing'] = null;
        $data['top_gainer_coin'] = null;
        $data['top_volume_coin'] = null;

        $usdtCoinDetails = Coin::where('coin_type', 'USDT')->first();
        $fiatCurrencyType = 'USD';
        $limit = 3;

        $currencyDetails = CurrencyList::where(['code' => strtoupper($fiatCurrencyType)])->first();

        if(isset($usdtCoinDetails))
        {
            $coinList = Coin::with(['coin_pair_usdt'=>function($query) use($usdtCoinDetails){
                                    $query->where('parent_coin_id', $usdtCoinDetails->id);
                                }])->get();

            $newCoinPairList = [];

            foreach($coinList as $coinDetails)
            {
                $temp = [];
                $temp['id'] = $coinDetails->id;
                $temp['coin_icon'] = createImageUrl(IMG_ICON_PATH, $coinDetails->coin_icon);
                $temp['coin_type'] = $coinDetails->coin_type;
                $temp['usdt_price'] = (isset($coinDetails->coin_pair_usdt)) ? convertCoinPriceToFiatCurrency($coinDetails->coin_pair_usdt->price, $currencyDetails) : convertCoinPriceToFiatCurrency($coinDetails->coin_price, $currencyDetails);
                $temp['change'] = (isset($coinDetails->coin_pair_usdt)) ? $coinDetails->coin_pair_usdt->change : 0;
                $temp['coin_created_at'] = $coinDetails->created_at;
                $temp['coin_pair_updated_at'] = (isset($coinDetails->coin_pair_usdt)) ? $coinDetails->coin_pair_usdt->updated_at : $coinDetails->created_at;
                $temp['currency_symbol'] = $currencyDetails->symbol;

                array_push($newCoinPairList, $temp);
            }
            $highlight_coin = $newCoinPairList;
            $new_listing = $newCoinPairList;
            $top_gainer_coin = $newCoinPairList;
            $top_volume_coin = $newCoinPairList;

            // latest coin pair updated at
            usort($highlight_coin,function($first,$second){
                return $first['coin_pair_updated_at'] < $second['coin_pair_updated_at'];
            });

            // new listing coin list
            usort($new_listing,function($first,$second){
                return $first['coin_created_at'] < $second['coin_created_at'];
            });

            // 24 hour top gain coin list
            usort($top_gainer_coin,function($first,$second){
                return $first['change'] < $second['change'];
            });

            // top volume coin list
            usort($top_volume_coin,function($first,$second){
                return $first['usdt_price'] < $second['usdt_price'];
            });

            $data['highlight_coin'] = array_slice($highlight_coin, 0, $limit);
            $data['new_listing'] = array_slice($new_listing, 0, $limit);
            $data['top_gainer_coin'] = array_slice($top_gainer_coin, 0, $limit);
            $data['top_volume_coin'] = array_slice($top_volume_coin, 0, $limit);

        }
        return $data;
    }

    public function getMarketOverviewTopCoinListWebsocketData($coinPair)
    {
        $data['coin_pair_details'] = null;
        try{
            $fiatCurrencyType = 'USD';

            $usdtCoinDetails = Coin::where('coin_type', 'USDT')->first();

            $currencyDetails = CurrencyList::where(['code' => strtoupper($fiatCurrencyType)])->first();

            if(!isset($currencyDetails))
            {
                return responseData(false, __('Fiat Currency details not found!'));
            }

            if(isset($usdtCoinDetails))
            {
                $coinDetails = CoinPair::where('parent_coin_id', $usdtCoinDetails->id)
                                        ->where('child_coin_id', $coinPair->child_coin_id)
                                        ->join('coins',['coin_pairs.child_coin_id'=>'coins.id'])
                                        ->select(['coin_pairs.id','coin_pairs.volume','coin_pairs.change','coin_pairs.high',
                                        'coin_pairs.low','coin_pairs.price','coins.coin_icon as coin_icon','coin_pairs.created_at',
                                        'coins.coin_type as coin_type','coins.id as coin_id'])->first();

                if ($coinDetails) {
                    $walletBalance = Wallet::where('coin_id', $coinDetails->coin_id)->sum('balance');

                    $coinDetails['total_balance'] = convertCoinPriceToFiatCurrency(($walletBalance * $coinDetails->price), $currencyDetails);
                    $coinDetails->price = convertCoinPriceToFiatCurrency($coinDetails->price, $currencyDetails);
                    $coinDetails->high = convertCoinPriceToFiatCurrency($coinDetails->high, $currencyDetails);
                    $coinDetails->low = convertCoinPriceToFiatCurrency($coinDetails->low, $currencyDetails);
                    if(isset($coinDetails->coin_icon))
                    {
                        $coinDetails->coin_icon = createImageUrl(IMG_ICON_PATH, $coinDetails->coin_icon);
                    }
                }
                $data['coin_pair_details'] = $coinDetails;
            }
        } catch (\Exception $e) {
            storeException('getMarketOverviewTopCoinListWebsocketData', $e->getMessage());
        }
        return $data;
    }

}
