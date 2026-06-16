<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StakingOfferRequest;
use App\Model\StakingInvestment;
use App\Model\StakingInvestmentPayment;
use Illuminate\Http\Request;
use App\Http\Services\CoinService;
use App\Http\Services\StakingOfferService;

class StakingOfferController extends Controller
{
    private $coinService;
    private $stakingOfferService;

    public function __construct()
    {
        $this->coinService = new CoinService;
        $this->stakingOfferService = new StakingOfferService;
    }
    public function createOffer()
    {
        $data['title'] = __('Create New Offer for Staking');
        $coin_response = $this->coinService->getAllActiveCoinList();
        if ($coin_response['success']) {
            $data['coin_list'] = $coin_response['data'];
        }
        return view('admin.staking.offer-management.create-offer', $data);
    }

    public function storeOffer(StakingOfferRequest $request)
    {
        $response = $this->stakingOfferService->saveOffer($request);

        if ($response['success']) {
            return redirect()->route('stakingOfferList')->with('success', $response['message']);
        } else {
            return back()->with('dismiss', $response['message'])->withInput($request->all());
        }
    }

    public function offerList()
    {
        $data['title'] = __('Offer List');
        $response = $this->stakingOfferService->getOfferList();
        if ($response['success']) {
            $data['offer_list'] = $response['data'];
        }
        return view('admin.staking.offer-management.offer-list', $data);
    }

    public function offerStatus(Request $request)
    {
        $response = $this->stakingOfferService->statusChange($request);
        return response()->json($response);
    }

    public function editOffer($uid)
    {
        $data['title'] = __('Update Offer');
        $response = $this->stakingOfferService->getOfferDetails($uid);

        if ($response['success']) {
            $coin_response = $this->coinService->getAllActiveCoinList();
            if ($coin_response['success']) {
                $data['coin_list'] = $coin_response['data'];
            }

            $data['offer_details'] = $response['data']['offer_details'];

            return view('admin.staking.offer-management.edit-offer', $data);
        }
    }

    public function deleteOffer($uid)
    {
        $response = $this->stakingOfferService->deleteOffer($uid);
        if ($response['success']) {
            return back()->with('success', $response['message']);
        } else {
            return back()->with('dismiss', $response['message']);
        }
    }

    public function investmentList(Request $request)
    {
        $this->stakingOfferService->makeCompleteInvestment();

        $data['sub_menu'] = 'staking_investment_status_' . $request->type ?? 0;
        $data['unpaid_status'] = $this->stakingOfferService->checkUnpaidStatus();
        if (isset($request->type) && $request->type != 0) {
            $title = getInvestmentStatusStaking($request->type) . ' ' . __('Investment List');
        } else {
            $title = __('All Investment List');
        }
        $data['title'] = $title;
        $data['type'] = $request->type ?? 0;
        $investment_list = StakingInvestment::when(isset($request->type) && $request->type != 0, function ($query) use ($request) {
            $query->where('status', $request->type);
        })->latest();

        if ($request->ajax()) {
            $data['items'] = $investment_list;

            return datatables($data['items'])
                ->addColumn('email', function ($item) {
                    return $item->email = isset($item->user) ? $item->user->email : __('N/A');
                })
                ->editColumn('period', function ($item) {
                    return $item->period = $item->period . ' days';
                })
                ->editColumn('offer_percentage', function ($item) {
                    return $item->offer_percentage = $item->offer_percentage . '%';
                })
                ->editColumn('terms_type', function ($item) {
                    return $item->terms_type = getTermsTypeListStaking($item->terms_type);
                })
                ->editColumn('minimum_maturity_period', function ($item) {
                    return $item->minimum_maturity_period = $item->minimum_maturity_period . ' days';
                })
                ->editColumn('investment_amount', function ($item) {
                    return $item->investment_amount = $item->investment_amount . $item->coin_type;
                })
                ->editColumn('earn_daily_bonus', function ($item) {
                    return $item->earn_daily_bonus = $item->earn_daily_bonus . $item->coin_type;
                })
                ->editColumn('total_bonus', function ($item) {
                    return $item->total_bonus = $item->total_bonus . $item->coin_type;
                })
                ->editColumn('status', function ($item) {
                    return getInvestmentStatusStaking($item->status);
                })
                ->editColumn('auto_renew_status', function ($item) {
                    return autoRenewList($item->auto_renew_status);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('auto_renew_from', function ($item) {
                    return $item->is_auto_renew == STAKING_IS_AUTO_RENEW ? $item->auto_renew_from : __('N/A');
                })
                ->make(true);
        }

        return view('admin.staking.investment.list', $data);
    }

    public function givePayment()
    {
        return $this->handlerResponseAndRedirect(function () {
            return $this->stakingOfferService->givePayment();
        });
    }

    public function stakingInvestmentPaymentList(Request $request)
    {
        $data['title'] = __('Payment History');

        $investmentPaymentList = StakingInvestmentPayment::latest();

        if ($request->ajax()) {

            $data['items'] = $investmentPaymentList;

            return datatables($data['items'])
                ->addColumn('email', function ($item) {
                    return $item->email = isset($item->user) ? $item->user->email : __('N/A');
                })
                ->editColumn('investment_id', function ($item) {
                    return '<a href="">' . $item->staking_investment_id . '</a>';
                })
                ->editColumn('is_auto_renew', function ($item) {
                    return autoRenewList($item->is_auto_renew);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('investment_status', function ($item) {
                    return getInvestmentStatusStaking($item->investment_status);
                })
                ->rawColumns(['investment_id'])
                ->make(true);
        }

        return view('admin.staking.investment.payment-list', $data);
    }

    public function landingSettings()
    {
        $data['title'] = __('Landing Page Settings');
        $data['settings'] = allsetting();

        return view('admin.staking.settings.landing-page-settings', $data);
    }

    public function landingSettingsUpdate(Request $request)
    {
        $response = $this->stakingOfferService->updateLandingSettings($request);

        if ($response['success']) {
            return back()->with('success', $response['message']);
        } else {
            return back()->with('dismiss', $response['message']);
        }
    }
}
