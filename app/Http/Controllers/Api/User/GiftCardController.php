<?php

namespace App\Http\Controllers\Api\User;

use App\Model\Faq;
use App\Model\Coin;
use App\Model\GiftCard;
use Illuminate\Http\Request;
use App\Model\GiftCardBanner;
use App\Model\GiftCardCategory;
use App\Http\Controllers\Controller;
use App\Http\Services\GiftCardService;
use App\Http\Requests\Api\User\GetRedeemCode;
use App\Http\Requests\Api\User\BuyGiftCardRequest;
use App\Http\Requests\Api\User\SendGiftCardRequest;
use App\Http\Requests\Api\User\UpdateGiftCardRequest;

class GiftCardController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new GiftCardService;
    }

    public function buyGiftCard(BuyGiftCardRequest $request)
    {
        try {
            $response = $this->service->buyGiftCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('buyGiftCard controller', $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function giftCards()
    {
        $response = $this->service->giftCards();
        return response()->json($response);
    }

    public function giftCardList(Request $request)
    {
        $response = $this->service->giftCardList($request);
        return response()->json($response);
    }

    public function buyGiftCardPageData(Request $request)
    {
        $response = $this->service->buyGiftCardPageData($request);
        return response()->json($response);
    }

    public function buyGiftCardPageWalletData(Request $request)
    {
        $response = $this->service->giftCardWalletData($request);
        return response()->json($response);
    }
    
    public function checkGiftCard(Request $request)
    {
        try {
            $response = $this->service->checkGiftCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('checkGiftCard controller', $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }
    
    public function redeemGiftCard(Request $request)
    {
        try {
            $response = $this->service->redeemGiftCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('checkGiftCard controller', $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function giftCardMainPageData()
    {
        $settings = settings();
        $data = [];
        $data['header'] = $settings['gif_card_main_page_header'] ?? "";
        $data['description'] = $settings['gif_card_main_page_description'] ?? "";
        $data['banner'] = isset($settings['gif_card_main_page_banner']) ? asset(IMG_PATH.($settings['gif_card_main_page_banner'])) : null ;

        $data['second_header'] = $settings['gif_card_second_main_page_header'] ?? "";
        $data['second_description'] = $settings['gif_card_second_main_page_description'] ?? "";
        $data['second_banner'] = isset($settings['gif_card_second_main_page_banner']) ? asset(IMG_PATH.($settings['gif_card_second_main_page_banner'])) : null ;
        $data['gif_card_redeem_description'] = isset($settings['gif_card_redeem_description']) ? $settings['gif_card_redeem_description'] : null ;
        $data['gif_card_add_card_description'] = isset($settings['gif_card_add_card_description']) ? $settings['gif_card_add_card_description'] : null ;
        $data['gif_card_check_card_description'] = isset($settings['gif_card_check_card_description']) ? $settings['gif_card_check_card_description'] : null ;


        $data['banners'] = GiftCardBanner::where(['status' => STATUS_ACTIVE])->with('category:uid,name')->get(['uid','category_id', 'title', 'sub_title','banner']);
        if(auth()->guard('api')->check()){
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
        }else{
            $data['my_cards'] = [];
        }
        
        $data['faq'] = Faq::where(['faq_type_id' => FAQ_TYPE_GIFT_CARD, 'status' => STATUS_ACTIVE])->get(['question','answer']);

        $data['banners']->map(function($q){
            $q->banner = isset($q->banner) ? asset(GIFT_CARD_BANNER.$q->banner) : null;
        });
        

        return response()->json(responseData(true, __("Buy card page data found"), $data));
    }

    public function addGiftCard(Request $request)
    {
        try {
            $response = $this->service->addGiftCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('addGiftCard controller', $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function allGiftCardThemePageData()
    {
        try {
            $response = $this->service->allGiftCardThemePage();
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("allGiftCardThemePageData", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function getGiftCardTheme(Request $request)
    {
        try {
            $response = $this->service->getGiftCardTheme($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("getGiftCardTheme", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function sendGiftCard(SendGiftCardRequest $request)
    {
        try {
            $response = $this->service->sendGiftCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("sendGiftCard", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function myGiftCardPageData()
    {
        try {
            $settings = settings();
            $data = [];
            $data['header'] = $settings['my_gift_card_page_header'] ?? "";
            $data['description'] = $settings['my_gift_card_page_description'] ?? "";
            $data['banner'] = isset($settings['my_gift_card_page_banner']) ? asset(IMG_PATH.($settings['my_gift_card_page_banner'])) : null ;
            return response()->json(responseData(true, __("Page data get successfully") ,$data));
        } catch (\Exception $e) {
            storeException("myGiftCardPageData", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function getGiftCardLearnMorePage()
    {
        try {
            $settings = settings();
            $data = [];
            $data['page'] = $settings['gift_card_learn_more_page'] ?? "";
            return response()->json(responseData(true, __("Learn more page data get successfully") ,$data));
        } catch (\Exception $e) {
            storeException("getGiftCardLearnMorePage", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

    public function updateGiftCard(UpdateGiftCardRequest $request)
    {
        try {
            $response = $this->service->updateCard($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("updateGiftCard", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }
 
    public function getRedeemCode(GetRedeemCode $request)
    {
        try {
            $response = $this->service->getRedeemCode($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("getRedeemCode", $e->getMessage());
            return response()->json(responseData(false,__('Something went wrong')));
        }
    }

}
