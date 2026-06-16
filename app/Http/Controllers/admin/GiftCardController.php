<?php

namespace App\Http\Controllers\admin;

use App\Model\Faq;
use App\Model\GiftCard;
use Illuminate\Http\Request;
use App\Model\GiftCardBanner;
use App\Model\GiftCardCategory;
use App\Http\Controllers\Controller;
use App\Http\Services\GiftCardService;
use App\Http\Requests\Admin\GiftCardBannerRequest;
use App\Http\Requests\Admin\GiftCardCategoryRequest;

class GiftCardController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new GiftCardService;
    }
    // Gift Card Dashboard Start
    public function giftCardDashboard()
    {
        $card                       = GiftCard::get();
        $data                       = [];
        $data['total_card']         = $card->count() ?? 0;
        $data['total_card_redeem']  = $card->where('status', GIFT_CARD_STATUS_REDEEMED)->where('lock','<>',STATUS_ACTIVE)->count() ?? 0;
        $data['total_card_active']  = $card->where('status', GIFT_CARD_STATUS_ACTIVE)->where('lock','<>',STATUS_ACTIVE)->count() ?? 0;
        $data['total_card_lock']    = $card->where('lock', STATUS_ACTIVE)->count() ?? 0;
        $data['total_card_transfer']= $card->where('lock', GIFT_CARD_STATUS_TRANSFARED)->where('lock','<>',STATUS_ACTIVE)->count() ?? 0;
        $data['total_card_trading'] = $card->where('lock', GIFT_CARD_STATUS_TRADING)->where('lock','<>',STATUS_ACTIVE)->count() ?? 0;
        $data['title']              = __("Gift Card Dashboard");
        return view('admin.gift_card.dashboard.dashboard',$data);
    }
    // Gift Card Dashboard Stop
    // Gift Card Header Start
    public function giftCardHeader(){
        $data['setting'] = settings();
        $data['title']   = __("User Side Header Settings");
        $data['tab']   = 'main_page';
        return view('admin.gift_card.header.header_list', $data);
    }
 
    public function giftCardHeaderSave(Request $request){
        try {
            $response = $this->service->giftCardHeaderSave($request);
            if($response['success']) return redirect(route('giftCardHeader'))->with('success', $response['message']);
            return redirect(route('giftCardHeader'))->with(['dismiss'=> $response['message']]);
        } catch (\Exception $e) {
            storeException('giftCardHeader controller', $e->getMessage());
            return redirect(route('giftCardHeader'))->with('dismiss', __("Something went wrong"));
        }
    }

    // Gift Card Header Stop
    // Gift Card Category Stop
    public function giftCardCategoryListPage(Request $request)
    {
        $data['title']              = __("Gift Card Category");
        try {
            if($request->wantsJson()){
                $data = GiftCardCategory::get();
                return datatables()->of($data)
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex align-items-center activity-menu justify-content-center">
                        <li class="viewuser"><a href="' . route('giftCardCategory', $item->uid) . '"><i class="fa fa-pencil"  style="font-size: 16px"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('giftCardCategoryDelete', $item->uid) . '"><i class="fa fa-trash"  style="font-size: 16px"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status'])
                ->make(true);
            }
        } catch (\Exception $e) {
            storeException('giftCardCategoryListPage', $e->getMessage());
        }
        return view('admin.gift_card.category.category_list',$data);
    }

    public function giftCardCategory($uid = null)
    {
        $data = [];
        $data['title'] = __("Gift Card Category");
        try {
            if($uid !== null)
            $data['category'] = GiftCardCategory::where('uid', $uid)->first();
        } catch (\Exception $e) {
            storeException('giftCardCategory', $e->getMessage());
        }
        return view('admin.gift_card.category.category',$data);
    }

    public function giftCardCategoryDelete($uid)
    {
        try {
            $response = $this->service->categoryDelete($uid);
            if($response['success']) return redirect(route('giftCardCategoryListPage'))->with('success', $response['message']);
            return redirect(route('giftCardCategoryListPage'))->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('giftCardCategory', $e->getMessage());
            return redirect(route('giftCardCategoryListPage'))->with('dismiss', __("Something went wrong"));
        }
    }

    public function giftCardCategorySave(GiftCardCategoryRequest $request)
    {
        try {
            $response = $this->service->categoryUpdateCreate($request);
            if($response['success']) return redirect(route('giftCardCategoryListPage'))->with('success', $response['message']);
            return redirect(route('giftCardCategoryListPage'))->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('giftCardCategory', $e->getMessage());
            return redirect(route('giftCardCategoryListPage'))->with('dismiss', __("Something went wrong"));
        }
    }
    // Gift Card Category Stop

    // Gift Card Banner Start
    public function giftCardBannerListPage(Request $request)
    {
        $data = [];
        $data['title'] = __("Gift Card Banner");
        try {
            if($request->wantsJson()){
                $type = $request->input('type');
                $data = null;
                if($type == 'all'){
                    $data = GiftCardBanner::with('category')->get();
                }else{
                    $data = GiftCardBanner::where('category_id', $type)->get();
                }

                return datatables()->of($data)
                ->addColumn('title', function ($item) {
                    return '<div class="text-left">
                    <span>'.$item->title.'</span>
                    </div>';
                })
                ->addColumn('category_id', function ($item) {
                    return $item?->category?->name ?? __("Not found");
                })
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex align-items-center activity-menu justify-content-center">
                        <li class="viewuser"><a href="' . route('giftCardBanner', $item->uid) . '"><i class="fa fa-pencil" style="font-size: 16px"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('giftCardBannerDelete', $item->uid) . '"><i class="fa fa-trash" style="font-size: 16px"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status', 'title'])
                ->make(true);
            }
            $data['categorys'] = GiftCardCategory::whereStatus(STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            storeException('giftCardBannerListPage', $e->getMessage());
        }
        return view('admin.gift_card.banner.banner_list', $data);
    }

    public function giftCardBanner($uid = null)
    {
        $data = [];
        $data['title'] = __("Gift Card Banner");
        try {
            if($uid !== null)
            $data['banner'] = GiftCardBanner::where('uid', $uid)->first();
            $data['categorys'] = GiftCardCategory::get();
        } catch (\Exception $e) {
            storeException('giftCardBanner', $e->getMessage());
        }
        return view('admin.gift_card.banner.banner',$data);
    }
    public function giftCardBannerDelete($uid)
    {
        try {
            $response = $this->service->bannerDelete($uid);
            if($response['success']) return redirect(route('giftCardBannerListPage'))->with('success', $response['message']);
            return redirect(route('giftCardBannerListPage'))->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('giftCardBanner', $e->getMessage());
            return redirect(route('giftCardBannerListPage'))->with('dismiss', __("Something went wrong"));
        }
    }
    public function giftCardBannerSave(GiftCardBannerRequest $request)
    {
        try {
            $response = $this->service->bannerUpdateCreate($request);
            if($response['success']) return redirect(route('giftCardBannerListPage'))->with('success', $response['message']);
            return redirect(route('giftCardBannerListPage'))->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('giftCardBanner', $e->getMessage());
            return redirect(route('giftCardBannerListPage'))->with('dismiss', __("Something went wrong"));
        }
    }
    // Gift Card Banner Stop
    // Gift Card History Start
    public function giftCardHistory(Request $request)
    {
        if($request->ajax()){
            $type  = $request->input('type') ?? 0;
            $cards = GiftCard::query();
            if($type == GIFT_CARD_STATUS_LOCKED)
                $cards = $cards->whereStatus(GIFT_CARD_STATUS_ACTIVE)->whereLock(STATUS_ACTIVE)->get();
            else
                $cards = $cards->whereStatus($type)->where('lock', '<>', STATUS_ACTIVE)->with('user:id,email')->get();
            return datatables()->of($cards)
                ->editColumn('wallet_type', function ($card) {
                    return getWalletGiftCard($card->wallet_type);
                })
                ->editColumn('user_id', function ($card) {
                    return $card?->user->email ?? __("Not found");
                })
                ->addColumn('status', function ($card) {
                    return getStatusGiftCard($card->status);
                })
                ->addColumn('lock', function ($card) {
                    return $card->lock ? __("Locked") : __("Unlocked");;
                })
                ->addColumn('created_at', function ($card) {
                    return $card->created_at;
                })
                ->rawColumns(['status','lock'])
                ->make(true);
        }
        $data['title'] = __("Gift Card History");
        return view('admin.gift_card.history.history',$data);
    }
    // Gift Card History End
    public function giftCardMainPageData()
    {
        $settings = settings();
        $data = [];
        $data['header'] = $settings['gif_card_main_page_header'] ?? "";
        $data['description'] = $settings['gif_card_main_page_description'] ?? "";
        $data['banner'] = isset($settings['gif_card_main_page_banner']) ? asset(IMG_PATH.($settings['gif_card_main_page_banner'])) : null;

        $data['my_cards'] = GiftCard::where(['user_id' => getUserId(), 'status' => STATUS_ACTIVE])->get();
        $data['faq'] = Faq::where(['faq_type_id' => FAQ_TYPE_GIFT_CARD, 'status' => STATUS_ACTIVE])->get();
        return $data;
    }

    public function learnMoreGiftCard()
    {
        $data = [];
        $data['setting'] = settings();
        $data['title'] = __("Learn more section");
        return view('admin.gift_card.learn_more.learn', $data);
    }

    public function processLearnMoreGiftCard(Request $request)
    {
        try {
            $response = $this->service->giftCardLearnMore($request);
            if($response['success']) return redirect(route('learnMoreGiftCard'))->with('success', $response['message']);
            return redirect(route('learnMoreGiftCard'))->with(['dismiss'=> $response['message']]);
        } catch (\Exception $e) {
            storeException('processLearnMoreGiftCard controller', $e->getMessage());
            return redirect(route('learnMoreGiftCard'))->with('dismiss', __("Something went wrong"));
        } 
    }
}
