<?php
namespace App\Http\Services;
use App\User;
use Exception;
use App\Model\Coin;
use App\Model\Wallet;
use App\Jobs\MailSend;
use App\Model\GiftCard;
use App\Model\AdminSetting;
use App\Model\GiftCardBanner;
use App\Model\GiftCardCategory;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;


class GiftCardService extends BaseService
{
    public function __construct()
    {
        //
    } 

    public function giftCards()
    {
        $settings = settings();
        $data = [];
        $data['header'] = $settings['gif_card_page_header'] ?? "";
        $data['description'] = $settings['gif_card_page_description'] ?? "";
        $data['banner'] = isset($settings['gif_card_page_banner']) ? asset(IMG_PATH.($settings['gif_card_page_banner'])) : null ;

        $data['banners'] = GiftCardBanner::where(['status' => STATUS_ACTIVE])->with('category:uid,name')->get(['uid','category_id', 'title', 'sub_title','banner']);
        $data['category_to_banners'] = GiftCardCategory::whereStatus(STATUS_ACTIVE)
                                        ->with('banner:category_id,uid,title,sub_title,banner')
                                        ->get(['uid', 'name']);
        $data['banners']->map(function($banner){
            $banner->banner = isset($banner->banner) ? asset(GIFT_CARD_BANNER.$banner->banner) : null;
        });
        $data['category_to_banners']->map(function($category){
            $category->banner->map(function($banner){
                $banner->banner = isset($banner->banner) ? asset(GIFT_CARD_BANNER.$banner->banner) : null;
            });
        });
        return responseData(true,__('Gift card banner get successfully'), $data);
    }

    public function giftCardWalletData($request)
    {
        if($coin_type = $request->coin_type ?? false){
            if($coin = Coin::where(['coin_type' => $coin_type, 'status' => STATUS_ACTIVE])->first()){
                $data = [];
                $id = auth()->id() ?? auth()->guard('api')->id();
                $data['exchange_wallet_balance'] = Wallet::whereUserId($id)->where('coin_type', $coin_type)->first()->balance ?? 0;
                if(Schema::hasTable('p2p_wallets') && class_exists(P2PWallet::class))
                    $data['p2p_wallet_balance'] = P2PWallet::whereUserId($id)->where('coin_type', $coin_type)->first()->balance ?? 0;
                
                return responseData(true,__('Wallet data get successfully'), $data);
            } return responseData(false,__('Coin type is invalid'));
        } return responseData(false,__('Coin type is required'));
    }
    
    public function giftCardList($request)
    {
        $limit = isset($request->limit) && is_numeric($request->limit) ? $request->limit : 20;
        $cards = GiftCard::with('banner:uid,title,sub_title,banner')->where('user_id', auth()->id() ?? auth()->guard('api')->id());
        if(isset($request->status) && $request->status != 'all'){
            if($request->status == GIFT_CARD_STATUS_LOCKED)
            $cards = $cards->where('lock', STATUS_ACTIVE)->whereStatus(GIFT_CARD_STATUS_ACTIVE);
            else $cards = $cards->where('status', $request->status)->where('lock','<>', STATUS_ACTIVE);
        }
        $cards = $cards->with(['banner:uid,title,sub_title,banner,category_id','banner.category:uid,name']);
        $cards = $cards->orderBy('created_at','DESC')->paginate($limit);
        $cards->map(function($card){
            $card->wallet_type = getWalletGiftCard($card->wallet_type);
            $card->_status     = ( $card->status == 1 ? ($card->lock == 1 ? 0 : 1) : 0);
            $card->_lock       = $card->lock ? 1 : 0;
            $card->lock_status = $card->status;
            $card->status      = getStatusGiftCard($card->status);
            $card->lock        = $card->lock ? __("Locked") : __("Unlocked");
            $card->banner->image = isset($card->banner->banner) ? asset(GIFT_CARD_BANNER.$card->banner->banner) : null;
        });
        return responseData(true,__('Gift card banner get successfully'), $cards);
    }

    // Gift Card Category Start
    public function categoryDelete($uid)
    {
        try {
            if($category = GiftCardCategory::where('uid', $uid)->first()){
                if(GiftCardBanner::where('category_id',$uid)->get()->count() > 0){
                    return responseData(false, __("This category deletion failed due to have banner"));
                }
                if($category->delete()) return responseData(true, __("Category deleted successfully"));
                return responseData(false, __("Category delete failed"));
            }
            return responseData(false, __("Category not found"));
        } catch (\Exception $e) {
            storeException("gift-card categoryDelete", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function categoryUpdateCreate($request)
    {
        try {
            $successMessage = responseData(true, __("Category created successfully"));
            $failedMessage  = responseData(true, __("Category create failed"));
            $finder         = [ 'uid' => $request->uid ?? "" ];
            $data           = [];
            $category       = null;
            if(! isset($request->uid)) $finder['uid'] = uniqid().date('').time();
            if(isset($request->uid)){
                $category       = GiftCardCategory::where('uid', $request->uid)->first();
                $successMessage = responseData(true, __("Category updated successfully"));
                $failedMessage  = responseData(true, __("Category update failed"));
            }
            $data['name']   = $request->name;
            $data['status'] = $request->status;
            $response = GiftCardCategory::updateOrCreate($finder, $data);
            if($response) return $successMessage;
            return $failedMessage;
        } catch (\Exception $e) {
            storeException("gift-card categoryUpdateCreate", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    // Gift Card Category Stop
    // Gift Card Banner Start
    public function bannerDelete($uid)
    {
        try {
            if($banner = GiftCardBanner::where('uid', $uid)->first()){
                $image  = $banner->banner ?? null;
                if(GiftCard::where('gift_card_banner_id',$uid)->get()->count() > 0){
                    return responseData(false, __("This banner deletion failed due to have user gift card"));
                }
                if($banner->delete()){
                    deleteFile(public_path(GIFT_CARD_BANNER), $image);
                    return responseData(true, __("Banner deleted successfully"));
                }
                return responseData(false, __("Banner delete failed"));
            } return responseData(false, __("Banner not found"));
        } catch (\Exception $e) {
            storeException("gift-card bannerDelete", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function bannerUpdateCreate($request)
    {
        try {
            $successMessage = responseData(true, __("Banner created successfully"));
            $failedMessage  = responseData(true, __("Banner create failed"));
            $finder         = [ 'uid' => $request->uid ?? "" ];
            $data           = [];
            $banner         = null;
            if(! isset($request->uid)) $finder['uid'] = uniqid().date('').time();
            if(isset($request->uid)){
                $banner          = GiftCardBanner::where('uid', $request->uid)->first();
                $successMessage  = responseData(true, __("Banner updated successfully"));
                $failedMessage   = responseData(true, __("Banner update failed"));
            }
            if($request->hasFile('banner')){
                $old_image       = ($banner !== null) ? $banner->banner : null;
                if($banner !== null) deleteFile(public_path(GIFT_CARD_BANNER), $old_image);
                $image           = uploadFile($request->file('banner'),GIFT_CARD_BANNER);
                $data['banner']  = $image;
            }
            $data['title']       = $request->title;
            $data['sub_title']   = $request->sub_title;
            $data['category_id'] = $request->category_id;
            $data['updated_by']  = $request->updated_by;
            $data['status']      = $request->status;
            $data['updated_by']  = auth()->id();
            $response            = GiftCardBanner::updateOrCreate($finder, $data);
            if($response) return $successMessage;
            return $failedMessage;
        } catch (\Exception $e) {
            storeException("gift-card bannerUpdateCreate", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    // Gift Card Banner Stop
    // Gift Card Api Start
    public function buyGiftCard($request)
    {
        try {
            $successMessage = responseData(true, __("Gift card created successfully"));
            $failedMessage  = responseData(true, __("Gift card create failed"));
            $id             = auth()->id() ?? auth()->guard('api')->id();
            $finder         = [ 'uid' => $request->uid ?? "" ];
            $data           = [];
            $gift_card      = null;
            $wallet         = null;
            $totalAmount    = 0;
            if(! isset($request->uid)){
                $finder['uid']       = uniqid().date('').time();
                $data['redeem_code'] = date('').time().rand(11111,99999);
                $data['wallet_type'] = $request->wallet_type;
                $data['user_id']     = $id;
                $data['owner_id']    = $id;
                $data['amount']      = $request->amount;
                $data['coin_type']   = $request->coin_type;
                $data['fees']        = 0;
                $data['gift_card_banner_id'] = $request->banner_id;
                $exchangeWallet      = Wallet::class;
                $userWallet          = null;
                match($request->wallet_type){
                    EXCHANGE_WALLET_TYPE => $userWallet = $exchangeWallet,
                    P2P_WALLET_TYPE      => $userWallet = Schema::hasTable('p2p_wallets') ? P2PWallet::class : $exchangeWallet,
                    default              => $userWallet = $exchangeWallet
                };
                if($wallet = $userWallet::where(['user_id' => $id, 'coin_type' => $request->coin_type])->first()){
                    $totalAmount = $request->amount;
                    if(!($wallet->balance > 0)) return responseData(false, __("You do not have enough balance"));
                    if(isset($request->bulk) && $request->bulk == GIFT_CARD_SINGLE_BUY){
                        if(!($wallet->balance > $request->amount)) return responseData(false, __("You do not have enough balance"));
                    }
                    if(isset($request->bulk) && $request->bulk == GIFT_CARD_BULK_BUY){
                        $totalAmount = ($request->amount * $request->quantity);
                        if(!($wallet->balance > $totalAmount)) return responseData(false, __("You do not have enough balance"));
                    }
                }else return responseData(false, __("Wallet not found"));
            }
            if(isset($request->uid)){
                $gift_card       = GiftCard::where('uid', $request->uid)->first();
                $successMessage  = responseData(true, __("Gift card updated successfully"));
                $failedMessage   = responseData(true, __("Gift card update failed"));
            }
            $data['note']     = $request->note ?? NULL;
            $data['lock']     = $request->lock;

            DB::beginTransaction();
            $response = null;
            if(isset($request->bulk) && $request->bulk == GIFT_CARD_SINGLE_BUY)
            $response = GiftCard::updateOrCreate($finder, $data);
            if(isset($request->bulk) && $request->bulk == GIFT_CARD_BULK_BUY){
                foreach(range(1,$request->quantity) as $loop){
                    $data['uid']       = uniqid().date('').time();
                    $data['redeem_code'] = date('').time().rand(11111,99999);
                    $response = GiftCard::create($data);
                }
            }
            if( $response && 
                (( isset($wallet) && $wallet->decrement('balance', $totalAmount)) XOR isset($request->uid))){
                DB::commit(); 
                $successMessage['data'] = GiftCard::where('user_id', (auth()->id() ?? auth()->guard('api')->id()))->orderBy('created_at', 'DESC')->get();
                $successMessage['data']->map(function($card){
                    $card->wallet_type = getWalletGiftCard($card->wallet_type);
                    $card->status      = getStatusGiftCard($card->status);
                    $card->lock        = $card->lock ? __("Locked") : __("Unlocked");
                });
                return $successMessage;
            }DB::rollBack(); return $failedMessage;
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('buyGiftCard service api', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function checkGiftCard($request)
    {
        try {
            if(isset($request->code)){
                if($card = GiftCard::where('redeem_code', $request->code)->first()){
                    $data['card'] = $card;
                    $data['banner'] = GiftCardBanner::where('uid', $card->gift_card_banner_id)->first();
                    $data['banner']->banner = isset($data['banner']->banner) ? asset(GIFT_CARD_BANNER.$data['banner']->banner) : null;
                    return responseData(true, __("Gift card details get successfully"), $data);
                } return responseData(false, __("Gift card not found"));
            } return responseData(false, __("Gift card code is required"));
        } catch (\Exception $e) {
            storeException('checkGiftCard service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    
    public function redeemGiftCard($request)
    {
        try {
            $response = $this->checkGiftCard($request);
            if(isset($response['success']) && !$response['success']) return $response;
            if(isset($response['data'])){
                $id     = auth()->id() ?? auth()->guard('api')->id();
                $card   = $response['data']['card'];
                $banner = $response['data']['banner'];
                if($card->lock) return responseData(false, __("Gift card is locked"));
                if($card->status == GIFT_CARD_STATUS_REDEEMED) return responseData(false, __("Gift card is already redeemed"));
                if($card->status == GIFT_CARD_STATUS_TRANSFARED) return responseData(false, __("Gift card already transfered"));
                if($card->status == GIFT_CARD_STATUS_TRADING) return responseData(false, __("Gift card active in trading"));
                DB::beginTransaction();
                $wallet = Wallet::where(['user_id' => $id, 'coin_type' => $card->coin_type])->first();
                if($wallet->increment('balance', $card->amount) && $card->update(['status' => GIFT_CARD_STATUS_REDEEMED]))
                { 
                    DB::commit(); 
                    return responseData(true, __("Gift card redeem successfully"));
                } 
                DB::rollBack(); 
                return responseData(false, __("Gift card redeem failed"));
            } return responseData(false, __("Gift card details not found"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('checkGiftCard service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    // Gift Card Api Stop

    // Gift Card Fronted Page Start
    public function giftCardHeaderSave($request)
    {
        try {
            // Gift Card Main Page
            if(isset($request->gif_card_main_page_header))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_main_page_header'],['value'=> $request->gif_card_main_page_header ?? ""]);
            if(isset($request->gif_card_main_page_description))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_main_page_description'],['value'=> $request->gif_card_main_page_description ?? ""]);
            if($request->hasFile('gif_card_main_page_banner')){
                $setting = settings(['gif_card_main_page_banner']);
                $old_image       = isset($setting) ? $setting['gif_card_main_page_banner'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_main_page_banner'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_main_page_banner'],['value'=> $image ?? ""]);
            }

            if(isset($request->gif_card_second_main_page_header))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_second_main_page_header'],['value'=> $request->gif_card_second_main_page_header ?? ""]);
            if(isset($request->gif_card_second_main_page_description))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_second_main_page_description'],['value'=> $request->gif_card_second_main_page_description ?? ""]);
            if($request->hasFile('gif_card_second_main_page_banner')){
                $setting = settings(['gif_card_second_main_page_banner']);
                $old_image       = isset($setting) ? $setting['gif_card_second_main_page_banner'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_second_main_page_banner'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_second_main_page_banner'],['value'=> $image ?? ""]);
            }

            if(isset($request->gif_card_redeem_description))
            {
                AdminSetting::updateOrCreate(['slug'=>'gif_card_redeem_description'],['value'=> $request->gif_card_redeem_description ?? ""]);
            }

            if(isset($request->gif_card_add_card_description))
            {
                AdminSetting::updateOrCreate(['slug'=>'gif_card_add_card_description'],['value'=> $request->gif_card_add_card_description ?? ""]);
            }

            if(isset($request->gif_card_check_card_description))
            {
                AdminSetting::updateOrCreate(['slug'=>'gif_card_check_card_description'],['value'=> $request->gif_card_check_card_description ?? ""]);
            }
            

            // Gift Card Create Page
            if(isset($request->gif_card_page_header))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header'],['value'=> $request->gif_card_page_header ?? ""]);
            if(isset($request->gif_card_page_description))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_page_description'],['value'=> $request->gif_card_page_description ?? ""]);
            
            if(isset($request->gif_card_page_header_feture_one))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_one'],['value'=> $request->gif_card_page_header_feture_one ?? ""]);
            if(isset($request->gif_card_page_header_feture_two))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_two'],['value'=> $request->gif_card_page_header_feture_two ?? ""]);
            if(isset($request->gif_card_page_header_feture_three))
            AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_three'],['value'=> $request->gif_card_page_header_feture_three ?? ""]);
            
            if($request->hasFile('gif_card_page_banner')){
                $setting = settings(['gif_card_page_banner']);
                $old_image       = isset($setting) ? $setting['gif_card_page_banner'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_page_banner'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_page_banner'],['value'=> $image ?? ""]);
            }
            if($request->hasFile('gif_card_page_header_feture_one_icon')){
                $setting = settings(['gif_card_page_header_feture_one_icon']);
                $old_image       = isset($setting) ? $setting['gif_card_page_header_feture_one_icon'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_page_header_feture_one_icon'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_one_icon'],['value'=> $image ?? ""]);
            }
            if($request->hasFile('gif_card_page_header_feture_two_icon')){
                $setting = settings(['gif_card_page_header_feture_two_icon']);
                $old_image       = isset($setting) ? $setting['gif_card_page_header_feture_two_icon'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_page_header_feture_two_icon'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_two_icon'],['value'=> $image ?? ""]);
            }
            if($request->hasFile('gif_card_page_header_feture_three_icon')){
                $setting = settings(['gif_card_page_header_feture_three_icon']);
                $old_image       = isset($setting) ? $setting['gif_card_page_header_feture_three_icon'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gif_card_page_header_feture_three_icon'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gif_card_page_header_feture_three_icon'],['value'=> $image ?? ""]);
            }

            // Gift Cards Themes Settings
            if(isset($request->themes_gift_card_page_header))
            AdminSetting::updateOrCreate(['slug'=>'themes_gift_card_page_header'],['value'=> $request->themes_gift_card_page_header ?? ""]);
            if(isset($request->themes_gift_card_page_description))
            AdminSetting::updateOrCreate(['slug'=>'themes_gift_card_page_description'],['value'=> $request->themes_gift_card_page_description ?? ""]);
            if($request->hasFile('themes_gift_card_page_banner')){
                $setting = settings(['themes_gift_card_page_banner']);
                $old_image       = isset($setting) ? $setting['themes_gift_card_page_banner'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('themes_gift_card_page_banner'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'themes_gift_card_page_banner'],['value'=> $image ?? ""]);
            }
            
            // My Gift Cards Themes Settings
            if(isset($request->my_gift_card_page_header))
            AdminSetting::updateOrCreate(['slug'=>'my_gift_card_page_header'],['value'=> $request->my_gift_card_page_header ?? ""]);
            if(isset($request->my_gift_card_page_description))
            AdminSetting::updateOrCreate(['slug'=>'my_gift_card_page_description'],['value'=> $request->my_gift_card_page_description ?? ""]);
            if($request->hasFile('my_gift_card_page_banner')){
                $setting = settings(['my_gift_card_page_banner']);
                $old_image       = isset($setting) ? $setting['my_gift_card_page_banner'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('my_gift_card_page_banner'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'my_gift_card_page_banner'],['value'=> $image ?? ""]);
            }

            return responseData(true, __("Header setting updated successfully"));
        } catch (\Exception $e) {
            storeException('giftCardHeaderSave service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    // Gift Card Fronted Page Stop

    public function addGiftCard($request)
    {
        try {
            $response = $this->checkGiftCard($request);
            if(isset($response['success']) && !$response['success']) return $response;
            if(isset($response['data'])){
                $id     = auth()->id() ?? auth()->guard('api')->id();
                $card   = $response['data']['card'];
                $banner = $response['data']['banner'];
                
                if($id ==$card->user_id)
                {
                    return responseData(false, __('You can not add your own gift card!'));
                }
                
                if($card->lock) return responseData(false, __("Gift card is locked"));
                if($card->status == GIFT_CARD_STATUS_REDEEMED) return responseData(false, __("Gift card is already redeemed"));
                if($card->status == GIFT_CARD_STATUS_TRANSFARED) return responseData(false, __("Gift card already transfered"));
                if($card->status == GIFT_CARD_STATUS_TRADING) return responseData(false, __("Gift card active in trading"));

                DB::beginTransaction();
                $data = [
                    'uid' => uniqid().date('').time(),
                    'gift_card_banner_id' => $banner->uid,
                    'coin_type' => $card->coin_type,
                    'wallet_type' => $card->wallet_type,
                    'amount' => $card->amount,
                    'fees' => $card->fees,
                    'redeem_code' => date('').time().rand(11111,99999),
                    'user_id' => $id,
                    'owner_id' => $id,
                    'status' => GIFT_CARD_STATUS_ACTIVE
                ];
                $gift_card = GiftCard::create($data);
                if($gift_card && $card->update(['status' => GIFT_CARD_STATUS_TRANSFARED])){
                    DB::commit(); 
                    return responseData(true, __("Gift card added successfully"));
                }
                DB::rollBack(); 
                return responseData(false, __("Gift card added failed"));
            } return responseData(false, __("Gift card details not found"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('addGiftCard service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function buyGiftCardPageData($request)
    {
        try {
            $banner = null;
            if(!isset($request->uid)) return responseData(false, __("Banner is required"));
            if(!($banner = GiftCardBanner::where('uid', $request->uid)->with('category:uid,name')->first())) return responseData(false, __("Banner not found"));
            $data = [];
            $settings = settings();

            // header
            $data['header'] = $settings['gif_card_page_header'] ?? "";
            $data['description'] = $settings['gif_card_page_description'] ?? "";
            $data['banner'] = isset($settings['gif_card_page_banner']) ? asset(IMG_PATH.($settings['gif_card_page_banner'])) : null ;
            // header feature
            $data['feature_one'] = $settings['gif_card_page_header_feture_one'] ?? "";
            $data['feature_one_icon'] = isset($settings['gif_card_page_header_feture_one_icon']) ? asset(IMG_PATH.($settings['gif_card_page_header_feture_one_icon'])) : null ;
            
            $data['feature_two'] = $settings['gif_card_page_header_feture_two'] ?? "";
            $data['feature_two_icon'] = isset($settings['gif_card_page_header_feture_two_icon']) ? asset(IMG_PATH.($settings['gif_card_page_header_feture_two_icon'])) : null ;
            
            $data['feature_three'] = $settings['gif_card_page_header_feture_three'] ?? "";
            $data['feature_three_icon'] = isset($settings['gif_card_page_header_feture_three_icon']) ? asset(IMG_PATH.($settings['gif_card_page_header_feture_three_icon'])) : null ;

            // sellected banner
            $banner->banner = isset($banner->banner) ? asset(GIFT_CARD_BANNER.$banner->banner) : null ;
            $data['selected_banner'] = $banner;
            $data['coins'] = Coin::whereStatus(STATUS_ACTIVE)->get(['name','coin_type']);
            $data['banners'] = GiftCardBanner::where(['category_id' => $banner?->category?->uid ?? '','status' => STATUS_ACTIVE])->with('category:uid,name')->get(['uid','category_id', 'title', 'sub_title','banner']);
            $data['coins']->map(function($coin){
                $coin->label = $coin->coin_type;
                $coin->value = $coin->coin_type;
            });
            $data['banners']->map(function($banner){
                $banner->banner = isset($banner->banner) ? asset(GIFT_CARD_BANNER.$banner->banner) : null ;
            });

            return responseData(true,__('Page data get successfully'), $data);
        } catch (\Exception $e) {
            storeException("buyGiftCardPageData service", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function allGiftCardThemePage()
    {
        try {
            $data = [];
            $settings = settings();
            $data['header'] = $settings['themes_gift_card_page_header'] ?? "";
            $data['description'] = $settings['themes_gift_card_page_description'] ?? "";
            $data['banner'] = isset($settings['themes_gift_card_page_banner']) ? asset(IMG_PATH.($settings['themes_gift_card_page_banner'])) : null ;

            $data['categories'] = GiftCardCategory::whereStatus(STATUS_ACTIVE)->get(['uid', 'name']);
            $data['categories']->map(function($category){
                $category->label = $category->name;
                $category->value = $category->uid;
            });
            return responseData(true, __("Themes gift cards get successfully"), $data);
        } catch (\Exception $e) {
            storeException('allGiftCardThemePage service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getGiftCardTheme($request)
    {
        try {
            $category = null;
            $limit = $request->limit ?? 20;
            if(!isset($request->uid)) return responseData(false, __("Category is required"));
            if(($request->uid !== 'all') && !($category = GiftCardCategory::where('uid', $request->uid)->first())) return responseData(false, __("Category not found"));
            $data = [];
             
            $banners = GiftCardBanner::query();
            $category = $request->uid == 'all' ? : $banners = $banners->where('category_id', $category->uid);
            $banners = $banners->orderBy('created_at', 'DESC')->paginate($limit);
            $banners->map(function($banner){
                $banner->banner = isset($banner->banner) ? asset(GIFT_CARD_BANNER.$banner->banner) : null; 
            });
            return responseData(true, __("Data get successfully"), $banners);
        } catch (\Exception $e) {
            storeException("getGiftCardTheme service", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function sendGiftCard($request)
    {
        try {
            if($card = GiftCard::where(['uid' => $request->card_uid, 'status' => STATUS_ACTIVE, 'user_id' => getCurrentUserId()])->with('banner')->first()){
                $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                if($request->send_by == GIFT_CARD_SEND_BY_EMAIL){
                    $title = __('You have received a new gift card');
                    $emailData = [
                        'company' => $companyName,
                        'card' => $card,
                        'to' => $request->to_email,
                        'name' => 'Test Name',
                        'subject' => $title,
                        'email_header' => $title,
                        'email_message' => $request->message ?? "",
                        'mailTemplate' => 'email.gift_card'
                    ];
                    dispatch(new MailSend($emailData))->onQueue('send-mail'); 
                }
                if($request->send_by == GIFT_CARD_SEND_BY_PHONE){
                    $message = __("Congrats! You received :coin :coin_type worth gift card from",[ 'coin' => $card->amount, 'coin_type' => $card->coin_type ]) . $companyName ;
                    $message .= __("Card redeem code is") .' : '. $card->reredeem_code.' .';
                    $message .= $request->message ?? '';
                    $sms = new SmsService();
                    $sms->send('+'.$request->to_phone, $message);
                }
                
                return responseData(true, __("Card sent successfully"));
            }
            return responseData(false, __("Card not found successfully"));
        } catch (\Exception $e) {
            storeException("sendGiftCard service", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function giftCardLearnMore($request)
    {
        try{
            if(isset($request->page))
                AdminSetting::updateOrCreate(['slug'=>'gift_card_learn_more_page'],['value'=> $request->page ?? ""]);
            return responseData(true, __("Page updated successfully"));
        } catch (\Exception $e) {
            storeException("giftCardLearnMore service", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function updateCard($request)
    {
        try {
            if($card = GiftCard::where(['uid' => $request->card_uid, 'user_id' => getCurrentUserId() ])->first()){
                if($card->status == GIFT_CARD_STATUS_REDEEMED) return responseData(false, __("Gift card is already redeemed"));
                if($card->status == GIFT_CARD_STATUS_TRANSFARED) return responseData(false, __("Gift card already transfered"));
                if($card->status == GIFT_CARD_STATUS_TRADING) return responseData(false, __("Gift card active in trading"));
                $updateData = [];
                if(isset($request->note)) $updateData['note'] = $request->note;
                $updateData['lock'] = $request->lock;
                DB::beginTransaction();
                if($card->update($updateData)){
                    DB::commit();
                    $data = [];
                    if(isset($request->from) && $request->from == 'home'){
                        $data['my_cards'] = GiftCard::where(['user_id' => getUserId(), 'status' => GIFT_CARD_STATUS_ACTIVE])
                        ->with(['banner:uid,title,sub_title,banner,category_id','banner.category:uid,name'])
                        // ->with('banner.category')
                        ->orderBy('created_at', 'DESC')
                        ->limit(6)->get(['uid','gift_card_banner_id','coin_type','amount','lock','status','created_at']);

                        $data['my_cards']->map(function($q){
                            $q->banner->image = isset($q->banner->banner) ? asset(GIFT_CARD_BANNER.$q->banner->banner): null;
                            $q->_lock = $q->lock ? 1 : 0;
                            $q->lock_status = $q->status;
                            $q->_status = ( $q->status == 1 ? ($q->lock == 1 ? 0 : 1) : 0);
                            $q->lock = $q->lock ? __("Locked") : __("Unlocked");
                            $q->status = getStatusGiftCard($q->status);
                        });
                    } return responseData(true, __("Card updated successfully"), $data);
                } return responseData(false, __("Card update failed"));
            } return responseData(false, __("Card not found"));
        } catch (\Exception $e) {
            storeException('updateCard service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getRedeemCode($request)
    {
        try {
            $user = DB::table('users')->where('id' ,getCurrentUserId())->first();
            if($card = GiftCard::where(['uid' => $request->card_uid, 'user_id' => $user->id])->first()){
                if(Hash::check($request->password , $user->password)){
                    return responseData(true, __("Redeem code get successfuly"),['redeem_code' => $card->redeem_code]); 
                } return responseData(false, __("Password do not match"));
            } return responseData(false, __("Card not found"));
        } catch (\Exception $e) {
            storeException('updateCard service', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}