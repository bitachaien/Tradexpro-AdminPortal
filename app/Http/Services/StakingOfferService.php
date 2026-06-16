<?php

namespace App\Http\Services;

use App\Model\StakingInvestment;
use App\Model\StakingInvestmentPayment;
use App\Model\StakingOffer;
use App\Model\Wallet;
use App\Model\Coin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\SettingRepository;
use App\Http\Repositories\WalletRepository;
use App\Http\Services\FaqService;
use App\Traits\ResponseFormatTrait;
use Exception;

class StakingOfferService
{
    use ResponseFormatTrait;

    private $settingRepository;
    private $faqService;

    public function __construct()
    {
        $this->faqService = new FaqService;
        $this->settingRepository = new SettingRepository;
    }
    public function saveOffer($request)
    {

        try {
            $check_offer = StakingOffer::where('uid', '<>', $request->uid)
                ->where('status', STATUS_ACTIVE)
                ->where('coin_type', $request->coin_type)
                ->where('period', $request->period)
                ->first();
            if (isset($check_offer)) {
                $response = ['success' => false, 'message' => __('You have already a offer in this period!')];
                return $response;
            }
            if (isset($request->uid)) {
                $offer_details = StakingOffer::where('uid', $request->uid)->first();
            } else {
                $offer_details = new StakingOffer;
                $offer_details->uid = generateUID();
            }

            $offer_details->created_by = auth()->user()->id;
            $offer_details->coin_type = $request->coin_type;
            $offer_details->period = $request->period;
            $offer_details->offer_percentage = $request->offer_percentage;
            $offer_details->minimum_investment = $request->minimum_investment;
            $offer_details->maximum_investment = $request->maximum_investment;
            $offer_details->terms_type = $request->terms_type;
            $offer_details->minimum_maturity_period = $request->minimum_maturity_period ?? $request->period;
            $offer_details->registration_before = $request->registration_before ?? 0;
            $offer_details->phone_verification = $request->phone_verification ?? 0;
            $offer_details->kyc_verification = $request->kyc_verification ?? 0;
            $offer_details->user_minimum_holding_amount = $request->user_minimum_holding_amount ?? 0;
            $offer_details->status = $request->status;
            $offer_details->terms_condition = $request->body;

            $offer_details->save();

            $response = ['success' => true, 'message' => __('Offer is saved Successfully!')];
        } catch (\Exception $e) {

            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('stakingSaveOffer', $e->getMessage());
        }

        return $response;
    }

    public function getOfferList()
    {
        $offer_list = StakingOffer::latest()->get();

        $response = ['success' => true, 'message' => __('Offer List'), 'data' => $offer_list];
        return $response;
    }

    public function getOfferListBySearchGroupWise($request)
    {
        $search = $request->search;

        $data['coin_type'] = StakingOffer::where('status', STATUS_ACTIVE)->distinct()->pluck('coin_type');

        $offer_list = StakingOffer::where('status', STATUS_ACTIVE)
            ->when(isset($search), function ($query) use ($search) {
                $query->where('coin_type', $search);
            })
            ->select(
                '*',
                DB::raw('(select sum(investment_amount) from staking_investments 
                                                where staking_offer_id = staking_offers.id and 
                                                status <>' . STAKING_INVESTMENT_STATUS_CANCELED . ' ) as total_investment_amount'),
                DB::raw('(select coins.coin_icon from coins where coins.coin_type = staking_offers.coin_type) as coin_icon')
            )
            ->latest()->get();

        $offer_list->map(function ($query) {
            if (isset($query->coin_icon)) {
                $query->coin_icon = show_image_path($query->coin_icon, 'coin/');
            }

            if ($query->maximum_investment - $query->total_investment_amount == 0) {
                $query['sold_status'] = 1;
            } else {
                $query['sold_status'] = 0;
            }
        });

        $data['offer_list'] = $offer_list->groupBy('coin_type');
        $response = ['success' => true, 'message' => __('Offer List by Search and group wise'), 'data' => $data];
        return $response;
    }

    public function statusChange($request)
    {
        $offer_details = StakingOffer::where('uid', $request->uid)->first();
        if (isset($offer_details)) {
            $offer_details->status = $offer_details->status == STATUS_ACTIVE ? STATUS_INACTIVE : STATUS_ACTIVE;
            $offer_details->save();

            $response = ['success' => true, 'message' => __('Status is changed successfully!')];
        } else {
            $response = ['success' => false, 'message' => __('Invalid Request')];
        }

        return $response;
    }

    public function getOfferDetails($uid)
    {
        $offer_details = StakingOffer::where('uid', $uid)
            ->select(
                '*',
                DB::raw('(select sum(investment_amount) from staking_investments 
                                        where staking_offer_id = staking_offers.id and 
                                        status <>' . STAKING_INVESTMENT_STATUS_CANCELED . ' ) as total_investment_amount'),
                DB::raw('(select coins.coin_icon from coins where coins.coin_type = staking_offers.coin_type) as coin_icon')
            )
            ->first();

        if (isset($offer_details)) {
            $offer_details->coin_icon = show_image_path($offer_details->coin_icon, 'coin/');
            $offer_details->terms_condition = strip_tags($offer_details->terms_condition);
            $offer_details->stake_date = Carbon::now()->format('Y-m-d H:i:s');
            $offer_details->value_date = Carbon::now()->addDay()->format('Y-m-d H:i:s');
            $offer_details->interest_period = 1;
            $offer_details->interest_end_date = Carbon::now()->addDay($offer_details->period + 1)->format('Y-m-d H:i:s');
            $data['offer_details'] = $offer_details;

            $offer_list = StakingOffer::where('coin_type', $offer_details->coin_type)->where('status', STATUS_ACTIVE)->get();
            $data['offer_list'] = $offer_list;

            $response = ['success' => true, 'message' => __('Offer Detaials!'), 'data' => $data];
        } else {
            $response = ['success' => false, 'message' => __('Invalid Request')];
        }

        return $response;
    }

    public function deleteOffer($uid)
    {
        try {

            $offer_details = StakingOffer::where('uid', $uid)->first();
            if (isset($offer_details)) {
                $investment_list = StakingInvestment::where('staking_offer_id', $offer_details->id)
                    ->get();

                if (isset($investment_list) && $investment_list->count() > 0) {
                    $response = responseData(false, __('You can not delete this offer as users already has invested!'));
                } else {
                    $offer_details->delete();
                    $response = responseData(true, __('Offer is deleted successfully!'));
                }
            } else {
                $response = responseData(false, __('Offer details not found!'));
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('stakingdeleteOffer', $e->getMessage());
        }
        return $response;
    }
    public function submitInvestment($request)
    {
        try {

            $offer_details = StakingOffer::where('uid', $request->uid)->where('status', STATUS_ACTIVE)->first();

            if (isset($offer_details)) {
                $user = auth()->user();

                $checkInvestmentRequirementResponse = $this->checkStakingInvestmentRequirement($offer_details, $user);

                if (!$checkInvestmentRequirementResponse['success']) {
                    return $checkInvestmentRequirementResponse;
                }

                $checkMaxAmountResponse = $this->getStakingAvailableAmount($offer_details, $request->amount);
                if (!$checkMaxAmountResponse['success']) {
                    return $checkMaxAmountResponse;
                }

                if ($request->amount >= $offer_details->minimum_investment) {
                    $user_wallet = Wallet::where('user_id', $user->id)->where('coin_type', $offer_details->coin_type)
                        ->first();

                    if (isset($user_wallet)) {
                        if ($user_wallet->balance >= $request->amount) {
                            $user_wallet->decrement('balance', $request->amount);

                            $staking_investment = new StakingInvestment;
                            $staking_investment->uid = generateUID();
                            $staking_investment->staking_offer_id = $offer_details->id;
                            $staking_investment->user_id = $user->id;
                            $staking_investment->coin_type = $offer_details->coin_type;
                            $staking_investment->period = $offer_details->period;
                            $staking_investment->offer_percentage = $offer_details->offer_percentage;
                            $staking_investment->terms_type = $offer_details->terms_type;
                            $staking_investment->minimum_maturity_period = $offer_details->minimum_maturity_period;
                            $staking_investment->auto_renew_status = $request->auto_renew_status;
                            $staking_investment->investment_amount = $request->amount;
                            $staking_investment->total_bonus = getTotalStakingBonus($offer_details, $request->amount);
                            $staking_investment->earn_daily_bonus = getTotalStakingBonus($offer_details, $request->amount) / $offer_details->period;

                            $staking_investment->save();

                            $response = ['success' => true, 'message' => __('You have successfully invested in this offer!')];
                        } else {
                            $response = responseData(false, __('You have not sufficent balance!'));
                        }
                    } else {
                        $response = responseData(false, __('Wallet is not found!'));
                    }
                } else {
                    $response = ['success' => false, 'message' => __('You have to invest minimum ') . $offer_details->minimum_investment . __(' amount')];
                }
            } else {
                $response = ['success' => false, 'message' => __('Invalid Request')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('submitInvestment', $e->getMessage());
        }
        return $response;
    }

    public function checkStakingInvestmentRequirement($offer_details, $user)
    {
        try {

            if ($offer_details->registration_before != 0) {
                $daysSinceCreated = Carbon::parse($user->created_at)->diffInDays(Carbon::now());

                if ($offer_details->registration_before > $daysSinceCreated) {
                    $response = ['success' => false, 'message' => __('Your account registration must have to ') . $offer_details->registration_before . __(' days before')];
                    return $response;
                }
            }

            if ($offer_details->phone_verification == STATUS_ACTIVE && $user->phone_verified != STATUS_ACTIVE) {
                $response = ['success' => false, 'message' => __('Your phone is not verified!')];
            }

            if ($offer_details->user_minimum_holding_amount != 0) {
                $wallet_details = Wallet::where('user_id', $user->id)->where('coin_type', $offer_details->coin_type)->first();
                if (isset($wallet_details)) {
                    if ($wallet_details->balance < $offer_details->user_minimum_holding_amount) {
                        $response = ['success' => false, 'message' => __('Your wallet must have to ') . $offer_details->user_minimum_holding_amount . ' ' . $offer_details->coin_type];
                        return $response;
                    }
                } else {
                    $response = ['success' => false, 'message' => __('Your wallet not found for this coin type!')];
                    return $response;
                }
            }

            if ($offer_details->kyc_verification == STATUS_ACTIVE) {
                $kyc_type_is = allsetting('kyc_type_is') ?? 0;

                if ($kyc_type_is == KYC_TYPE_PERSONA) {
                    $checkVerificationStatus = checkThirdPartyVerificationStatus($user);
                    if (!$checkVerificationStatus['success']) {
                        return $checkVerificationStatus;
                    }
                } else {
                    $kycVerification = getKYCVerificationActiveList('kyc_staking_setting_status');
                    $userVerification = userVerificationActiveList($user);

                    if ($kycVerification['success'] == true) {
                        $kycVerificationActiveList = json_decode($kycVerification['data']);

                        if (isset($kycVerificationActiveList)) {
                            foreach ($kycVerificationActiveList as $item) {
                                if ($item == KYC_PHONE_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('Phone is not verified'), 'data' => KYC_PHONE_VERIFICATION];
                                    return $response;
                                }
                                if ($item == KYC_EMAIL_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('Email is not verified'), 'data' => KYC_EMAIL_VERIFICATION];
                                    return $response;
                                }
                                if ($item == KYC_NID_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('NID is not verified'), 'data' => KYC_NID_VERIFICATION];
                                    return $response;
                                }
                                if ($item == KYC_PASSPORT_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('Passport is not verified'), 'data' => KYC_PASSPORT_VERIFICATION];
                                    return $response;
                                }
                                if ($item == KYC_DRIVING_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('Driving licence is not verified'), 'data' => KYC_DRIVING_VERIFICATION];
                                    return $response;
                                }
                                if ($item == KYC_VOTERS_CARD_VERIFICATION && !in_array($item, $userVerification)) {
                                    $response = ['success' => false, 'message' => __('Voter Card is not verified'), 'data' => KYC_VOTERS_CARD_VERIFICATION];
                                    return $response;
                                }
                            }
                        }
                    }
                }
                //return $kyc_type_is;
            }

            $response = ['success' => true, 'message' => __('All verification is successful!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('checkStakingInvestmentRequirement', $e->getMessage());
        }
        return $response;
    }

    public function getStakingAvailableAmount($offer_details, $amount)
    {
        $offer_details->maximum_investment;
        $total_investment_amount = StakingInvestment::where('staking_offer_id', $offer_details->id)
            ->where('status', '<>', STAKING_INVESTMENT_STATUS_CANCELED)->sum('investment_amount');

        $available_amount = $offer_details->maximum_investment - $total_investment_amount;

        if ($amount <= $available_amount) {
            $response = ['success' => true, 'message' => __('Balance Available')];
        } else {
            $response = ['success' => false, 'message' => __('Investment amount is not available!')];
        }
        return $response;
    }

    public function getInvestmentListOfUserByPaginate($request)
    {
        if (
            isset($request->status) && !in_array($request->status, [
                STAKING_INVESTMENT_STATUS_RUNNING,
                STAKING_INVESTMENT_STATUS_CANCELED,
                STAKING_INVESTMENT_STATUS_PAID
            ])
        ) {
            return responseData(false, __('Invalid Status!'));
        }
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;
        $user = auth()->user();
        $status = $request->status;

        $investment_list = StakingInvestment::where('user_id', $user->id)
            ->when(isset($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $investment_list->map(function ($query) {
            $query->end_date = $query->created_at->addDay($query->period);

            $remain_days = $query->period - Carbon::parse($query->created_at)->diffInDays(Carbon::now());

            $query->remain_interest_day = $remain_days > 0 ? $remain_days : 0;
        });

        $response = responseData(true, __('User Investment list by paginate!'), $investment_list);
        return $response;
    }

    public function investmentDetails($request)
    {
        if (isset($request->uid)) {
            $investment_details = StakingInvestment::where('uid', $request->uid)->first();

            if (isset($investment_details)) {
                $investment_details->end_date = $investment_details->created_at->addDay($investment_details->period);
                $remain_days = $investment_details->period - Carbon::parse($investment_details->created_at)->diffInDays(Carbon::now());

                $investment_details->remain_interest_day = $remain_days > 0 ? $remain_days : 0;

                $response = responseData(true, __('Investment Details'), $investment_details);
            } else {
                $response = responseData(false, __('Invalid Request!'));
            }
        } else {
            $response = responseData(false, __('UID is missing!'));
        }

        return $response;
    }

    public function canceledInvestment($request)
    {
        try {
            if (isset($request->uid)) {
                $user = auth()->user();
                $investmentDetails = StakingInvestment::where('user_id', $user->id)
                    ->where('uid', $request->uid)->first();
                if (isset($investmentDetails)) {
                    if ($investmentDetails->status == STAKING_INVESTMENT_STATUS_RUNNING) {
                        $user_wallet = Wallet::where('user_id', $user->id)->where('coin_type', $investmentDetails->coin_type)
                            ->first();
                        if (isset($user_wallet)) {
                            $daysSinceCreated = Carbon::parse($investmentDetails->created_at)->diffInDays(Carbon::now());
                            $total_amount = 0;
                            $total_bonus = 0;
                            if ($investmentDetails->terms_type == STAKING_TERMS_TYPE_STRICT && $daysSinceCreated >= $investmentDetails->period) {
                                $total_amount = $investmentDetails->investment_amount + $investmentDetails->total_bonus;
                                $total_bonus = $investmentDetails->total_bonus;
                            } elseif ($investmentDetails->terms_type == STAKING_TERMS_TYPE_FLEXIBLE && $daysSinceCreated >= $investmentDetails->minimum_maturity_period) {
                                $total_amount = $investmentDetails->investment_amount + $investmentDetails->earn_daily_bonus * $daysSinceCreated;
                                $total_bonus = $investmentDetails->earn_daily_bonus * $daysSinceCreated;
                            } else {
                                $total_amount = $investmentDetails->investment_amount;
                                $total_bonus = 0;
                            }

                            $payment_history = new StakingInvestmentPayment;
                            $payment_history->uid = generateUID();
                            $payment_history->user_id = $investmentDetails->user_id;
                            $payment_history->staking_investment_id = $investmentDetails->id;
                            $payment_history->wallet_id = $user_wallet->id;
                            $payment_history->coin_type = $investmentDetails->coin_type;
                            $payment_history->is_auto_renew = $investmentDetails->auto_renew_status;
                            $payment_history->total_investment = $investmentDetails->investment_amount;
                            $payment_history->total_bonus = $total_bonus;
                            $payment_history->total_amount = $total_amount;
                            $payment_history->investment_status = STAKING_INVESTMENT_STATUS_CANCELED;
                            $payment_history->save();

                            $user_wallet->increment('balance', $total_amount);

                            $investmentDetails->status = STAKING_INVESTMENT_STATUS_CANCELED;
                            $investmentDetails->save();

                            $response = responseData(true, __('Investment is canceled successfully!'));
                        } else {
                            $response = responseData(false, __('Wallet is not found!'));
                        }
                    } elseif ($investmentDetails->status == STAKING_INVESTMENT_STATUS_CANCELED) {
                        $response = responseData(false, __('You already canceled this investment!'));
                    } else {
                        $response = responseData(false, __('You already get paid from this invesmt!'));
                    }
                } else {
                    $response = responseData(false, __('Investment details is not found!'));
                }
            } else {
                $response = responseData(false, __('UID is missing!'));
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('canceledInvestment', $e->getMessage());
        }

        return $response;
    }

    public function makeCompleteInvestment()
    {
        $investmentList = StakingInvestment::where('status', STAKING_INVESTMENT_STATUS_RUNNING)->get();

        foreach ($investmentList as $investment) {
            $daysSinceCreated = Carbon::parse($investment->created_at)->diffInDays(Carbon::now());

            if ($daysSinceCreated >= $investment->period) {

                DB::beginTransaction();
                try {
                    if ($investment->auto_renew_status == STAKING_IS_AUTO_RENEW) {
                        // Create a new instance with the same data
                        $newInvestment = $investment->replicate();

                        // Modify unique or non-duplicable fields
                        $newInvestment->uid = generateUID();
                        $newInvestment->staking_offer_id = $investment->id;
                        $newInvestment->auto_renew_from = $investment->uid;
                        $newInvestment->is_auto_renew = STAKING_IS_AUTO_RENEW;
                        $newInvestment->save();
                    }
                    $investment->update(['status' => STAKING_INVESTMENT_STATUS_UNPAID]);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();

                    storeLog(processExceptionMsg($e), 'error');
                    continue;
                }
            }
        }
        return $this->responseData(true, __('Investment Status Make Unpaid by checking period'));
    }

    public function checkUnpaidStatus()
    {
        $investmentList = StakingInvestment::where('status', STAKING_INVESTMENT_STATUS_UNPAID)->get();
        if (isset($investmentList) && $investmentList->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function givePayment()
    {
        $investmentList = StakingInvestment::where('status', STAKING_INVESTMENT_STATUS_UNPAID)->get();

        foreach ($investmentList as $investment) {
            $user_wallet = (new WalletRepository)->walletInfo($investment->user_id, $investment->coin_type);

            $total_amount = ($investment->auto_renew_status == STAKING_IS_AUTO_RENEW)
                ? $investment->total_bonus
                : $investment->investment_amount + $investment->total_bonus;

            DB::beginTransaction();
            try {
                StakingInvestmentPayment::create([
                    'uid' => generateUID(),
                    'user_id' => $investment->user_id,
                    'staking_investment_id' => $investment->id,
                    'wallet_id' => $user_wallet->id,
                    'coin_type' => $investment->coin_type,
                    'is_auto_renew' => $investment->auto_renew_status,
                    'total_investment' => $investment->investment_amount,
                    'total_bonus' => $investment->total_bonus,
                    'total_amount' => $total_amount,
                    'investment_status' => STAKING_INVESTMENT_STATUS_SUCCESS
                ]);

                $user_wallet->increment('balance', $total_amount);

                $investment->update(['status' => STAKING_INVESTMENT_STATUS_PAID]);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                storeLog(processExceptionMsg($e), 'error');
                continue;
            }
        }
        return $this->responseData(true, __('Payment is sent to user successfully!'));
    }

    public function earningListUserByPaginate($request)
    {
        $user = auth()->user();
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;

        if (isset($request->type) && $request->type = 'success') {
            $data['earning_list'] = StakingInvestmentPayment::where('user_id', $user->id)
                ->select(DB::raw('coin_type, sum(total_amount) as total_amount, 
                                                                sum(total_investment) as total_investment, 
                                                                sum(total_bonus) as total_bonus'))
                ->groupBy('coin_type')
                ->paginate($limit, ['*'], 'page', $offset);

            $response = responseData(true, __('Pending Earning List by paginate'), $data);
        } elseif (isset($request->type) && $request->type = 'pending') {
            $data['earning_list'] = StakingInvestment::where('user_id', $user->id)
                ->where('status', '<>', STAKING_INVESTMENT_STATUS_CANCELED)
                ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment, 
                                                        sum(total_bonus) as total_bonus,
                                                        sum(investment_amount + total_bonus) as total_amount'))
                ->groupBy('coin_type')
                ->paginate($limit, ['*'], 'page', $offset);

            $response = responseData(true, __('Pending Earning List by paginate'), $data);
        } else {
            $response = responseData(false, __('Earning type is required!'));
        }
        return $response;
    }

    public function earningStatisticsUser()
    {
        $user = auth()->user();

        $data['total_investment'] = StakingInvestment::where('user_id', $user->id)
            ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment'))
            ->groupBy('coin_type')
            ->get();
        $data['total_running_investment'] = StakingInvestment::where('user_id', $user->id)
            ->where('status', STAKING_INVESTMENT_STATUS_RUNNING)
            ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment'))
            ->groupBy('coin_type')
            ->get();
        $data['total_paid_investment'] = StakingInvestment::where('user_id', $user->id)
            ->where('status', STAKING_INVESTMENT_STATUS_PAID)
            ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment'))
            ->groupBy('coin_type')
            ->get();
        $data['total_unpaid_investment'] = StakingInvestment::where('user_id', $user->id)
            ->where('status', STAKING_INVESTMENT_STATUS_UNPAID)
            ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment'))
            ->groupBy('coin_type')
            ->get();
        $data['total_cancel_investment'] = StakingInvestment::where('user_id', $user->id)
            ->where('status', STAKING_INVESTMENT_STATUS_CANCELED)
            ->select(DB::raw('coin_type, 
                                                        sum(investment_amount) as total_investment'))
            ->groupBy('coin_type')
            ->get();

        $response = responseData(true, __('Staking Investment Statistics!'), $data);
        return $response;
    }

    public function updateLandingSettings($request)
    {
        $response = $this->settingRepository->saveAdminSetting($request);
        return $response;
    }

    public function landingDetails()
    {
        $settings = allsetting();

        $data['staking_landing_title'] = $settings['staking_landing_title'] ?? null;
        $data['staking_landing_description'] = $settings['staking_landing_description'] ?? null;
        $data['staking_landing_cover_image'] = isset($settings['staking_landing_cover_image']) ? asset(path_image() . $settings['staking_landing_cover_image']) : null;

        $data['faq_list'] = $this->faqService->getActiveStakingFAQList()['data'];

        $response = responseData(true, __('Landing Page Details'), $data);
        return $response;
    }

    public function investmentGetPaymentList($request)
    {
        $user = auth()->user();
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;

        $earning_list = StakingInvestmentPayment::where('user_id', $user->id)
            ->where('investment_status', STAKING_INVESTMENT_STATUS_SUCCESS)
            ->paginate($limit, ['*'], 'page', $offset);

        $response = responseData(true, __('Investment Get Payment History'), $earning_list);
        return $response;
    }

    public function getTotalInvestmentBonus($request)
    {
        $offer_details = StakingOffer::where('uid', $request->uid)->where('status', STATUS_ACTIVE)->first();

        if (isset($offer_details)) {
            $data['total_bonus'] = getTotalStakingBonus($offer_details, $request->amount);
            $response = responseData(true, __('Total Bonus from this staking'), $data);
        } else {
            $response = responseData(false, __('Invalid Offer Request!'));
        }
        return $response;
    }
}
