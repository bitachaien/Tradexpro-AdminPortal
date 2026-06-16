<?php

namespace App\Http\Repositories;

use App\Http\Services\Logger;
use App\Model\AffiliationCode;
use App\Model\AffiliationHistory;
use App\Model\BuyCoinReferralHistory;
use App\Model\ReferralSignBonusHistory;
use App\Model\ReferralUser;
use App\Model\TradeReferralHistory;
use App\Model\Wallet;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AffiliateRepository
{
    public function __construct() {
    }
    // create affiliation code
    public function create($userId)
    {
        $affiliateCodeInput['user_id'] = $userId;
        $affiliateCodeInput['code'] = uniqid($userId);
        $affiliateCodeInput['status'] = 1;

        try {
            $created = AffiliationCode::create($affiliateCodeInput)->id;
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == '1062') {
                $this->create($userId);
            }
        }
        return $created;

    }

    // create referral user
    public function createReferralUser($userId, $parentId)
    {
        $created = 0;
        try {
            $data['user_id'] = $userId;
            $data['parent_id'] = $parentId;
            $created = ReferralUser::create($data)->id;

        } catch (\Exception $e) {

        }
        return $created;
    }

    // store data to affiliation history
    public function storeAffiliationHistory($transaction = null)
    {
        if ($transaction != null && $transaction->wallet->type == PERSONAL_WALLET) {
            $adminSettings = $this->checkAdminSettings();
            $withdrawalUser = $transaction->wallet->user->id;
            $transactionId = $transaction->transaction_hash;
            $coinType = $transaction->coin_type;
            $maxReferralLevel = max_level();
            try {
                $userAffiliation = $this->parentReferrals($maxReferralLevel, $withdrawalUser);
                if (!empty($userAffiliation)) {
                    $this->calculateReferralFees($adminSettings, $transactionId, $userAffiliation, $transaction->fees,  $maxReferralLevel, $coinType);
                }
            } catch (\Exception $e) {
                storeException('storeAffiliationHistory ',$e->getMessage());
            }
        }

        return 1;
    }

    // check referral fees setting
    public function checkAdminSettings()
    {
        $adminSettings = allsetting(['fees_level1', 'fees_level2', 'fees_level3', 'fees_level4', 'fees_level5', 'fees_level6', 'fees_level7', 'fees_level8', 'fees_level9', 'fees_level10']);
        if (empty($adminSettings['fees_level1'])) {
            $adminSettings['fees_level1'] = 10;
        }
        if (empty($adminSettings['fees_level2'])) {
            $adminSettings['fees_level2'] = 5;
        }
        if (empty($adminSettings['fees_level3'])) {
            $adminSettings['fees_level3'] = 10;
        }
        if (empty($adminSettings['fees_level4'])) {
            $adminSettings['fees_level4'] = 0;
        }
        if (empty($adminSettings['fees_level5'])) {
            $adminSettings['fees_level5'] = 0;
        }
        if (empty($adminSettings['fees_level6'])) {
            $adminSettings['fees_level6'] = 0;
        }
        if (empty($adminSettings['fees_level7'])) {
            $adminSettings['fees_level7'] = 0;
        }
        if (empty($adminSettings['fees_level8'])) {
            $adminSettings['fees_level8'] = 0;
        }
        if (empty($adminSettings['fees_level9'])) {
            $adminSettings['fees_level9'] = 0;
        }
        if (empty($adminSettings['fees_level10'])) {
            $adminSettings['fees_level10'] = 0;
        }
        return $adminSettings;
    }



    // get parent referral
    public function parentReferrals($maxReferralLevel = 1, $user_id)
    {
        $affiliation = DB::table('referral_users AS ru1')
            ->where('ru1.user_id', $user_id);

        $selectQuery = 'ru1.user_id as user_id, ru1.parent_id as parent_level_user_1';
        for ($i = 1; $i < $maxReferralLevel; $i++) {
            $ru_parent = "ru" . ($i + 1);
            $ru = "ru" . $i;
            $parent_level_user = 'parent_level_user_' . ($i + 1);
            $affiliation = $affiliation->leftJoin("referral_users AS $ru_parent", "$ru.parent_id", '=', "$ru_parent.user_id");
            $selectQuery = $selectQuery . ',' . " $ru_parent.parent_id as $parent_level_user";
        }
        $data = $affiliation->select(DB::raw($selectQuery))->first();

        return $data;
    }

    // calculate referral fees
    protected function calculateReferralFees($adminSettings, $transactionId, $affiliateUsers, $systemFees, $maxReferralLevel = 1, $coinType)
    {
        try {

        } catch (\Exception $e) {
            return 1;
        }

        $affiliationHistoryData['system_fees'] = $systemFees;
        $affiliationHistoryData['child_id'] = $affiliateUsers->user_id;
        $affiliationHistoryData['status'] = STATUS_ACTIVE;
        $affiliationHistoryData['transaction_id'] = $transactionId;
        $affiliationHistoryData['order_type'] = 1;
        $affiliationHistoryData['coin_type'] = $coinType;


        for ($i = 1; $i <= $maxReferralLevel; $i++) {
            $parent_level = 'parent_level_user_' . $i;
            $fees_level = 'fees_level' . $i;
            if ($affiliateUsers->{$parent_level}) {
                try {
                    $affiliationHistoryData['user_id'] = $affiliateUsers->{$parent_level};
                    $fees_percent = isset($adminSettings[$fees_level]) ? $adminSettings[$fees_level] : '0';
                    $affiliationHistoryData['amount'] = ($systemFees * $fees_percent) / 100;
                    $affiliationHistoryData['level'] = $i;
                    $userWallet = get_primary_wallet($affiliationHistoryData['user_id'], $coinType);
                    if (isset($userWallet)) {
                        $affiliationHistoryData['wallet_id'] = $userWallet->id;
                        $userWallet->increment('balance',$affiliationHistoryData['amount']);
                    }
                    AffiliationHistory::create($affiliationHistoryData);
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                break;
            }
        }
        return 1;
    }


    // deposit the affiliation fees
    public function depositAffiliationFees()
    {
        $firstDay = $start = Carbon::now()->startOfMonth();

        $limit = 100;
        while (true) {
            $affiliationHistory = AffiliationHistory::where('created_at', '<', $firstDay)
                ->where('status', 0)
                ->select('user_id', DB::raw('SUM(amount) AS total'))
                ->groupBy('user_id')
                ->limit($limit)
                ->pluck('total', 'user_id');
            $affiliationHistory = $affiliationHistory->toArray();

            $eligibleUsers = array_keys($affiliationHistory);


            $userWallets = Wallet::whereIn('user_id', $eligibleUsers)
                ->where('is_primary', '1')
                ->get();


            foreach ($userWallets as $userWallet) {
                $userWallet->referral_balance = ($userWallet->referral_balance + $affiliationHistory[$userWallet->user_id]);
                $userWallet->save();
                storeBotException('This user get bonus ', $affiliationHistory[$userWallet->user_id]. ' amount and the wallet id is '.$userWallet->id. ' and user id is '.$userWallet->user_id);
                AffiliationHistory::where('created_at', '<', $firstDay)
                    ->where('status', 0)
                    ->where('user_id', $userWallet->user_id)
                    ->update(['status' => 1]);
            }

            if (count($affiliationHistory) < $limit) {
//
                break;
            }
        }

    }


    // referral children
    public function childrenReferralQuery($maxReferralLevel = 1)
    {

//        $maxReferralLevel = 3;
        $referralAll = DB::table('referral_users AS ru1')->where('ru1.deleted_at', null);
        $selectQuery = 'COUNT(DISTINCT(ru1.user_id)) as level1';
        $allSumQuery = 'COUNT(parent_id) AS referralsLevel0, SUM(level1) as  referralsLevel1';

        for ($i = 1; $i < $maxReferralLevel; $i++) {
            $ru_child = "ru" . ($i + 1);
            $ru = "ru" . $i;
            $level = 'level' . ($i + 1);
            $referralsLevel = 'referralsLevel' . ($i + 1);

            $referralAll->leftJoin("referral_users AS $ru_child", "$ru.user_id", '=', "$ru_child.parent_id");
            $selectQuery = $selectQuery . ', ' . "COUNT(DISTINCT($ru_child.user_id)) as $level";
            $allSumQuery = $allSumQuery . ', ' . "SUM($level) as $referralsLevel";
        }

        $data['referral_all'] = $referralAll;
        $data['select_query'] = $selectQuery;
        $data['all_sum_query'] = $allSumQuery;
        return $data;
    }

    // store data to affiliation history for buy coin
    public function storeAffiliationHistoryForBuyCoin($transaction)
    {
        if ($transaction) {
            $adminSettings = $this->checkAdminSettings();
            $user_id = $transaction->user_id;
            $maxReferralLevel = $transaction->referral_level ;
            try {
                $userAffiliation = $this->parentReferrals($maxReferralLevel, $user_id);
                if (!empty($userAffiliation)) {
                    $this->calculateReferralFeesForBuyCoin($adminSettings, $transaction, $userAffiliation, $transaction->bonus,  $maxReferralLevel);
                }
            } catch (\Exception $e) {
                storeException('storeAffiliationHistoryForBuyCoin ex',$e->getMessage());
            }
        }

        return 1;
    }

    // calculate referral fees when buy coin
    protected function calculateReferralFeesForBuyCoin($adminSettings, $transaction, $affiliateUsers, $systemFees, $maxReferralLevel= 1 )
    {
        try {

        } catch (\Exception $e) {
            return 1;
        }
        $affiliationHistoryData['buy_id'] = $transaction->id;
        $affiliationHistoryData['phase_id'] = $transaction->phase_id;
        $affiliationHistoryData['system_fees'] = $systemFees;
        $affiliationHistoryData['child_id'] = $affiliateUsers->user_id;
        $affiliationHistoryData['status'] = STATUS_ACTIVE;

        for ($i = 1; $i <= $maxReferralLevel; $i++) {

            $parent_level = 'parent_level_user_' . $i;
            $fees_level = 'fees_level' . $i;

            if ($affiliateUsers->{$parent_level}) {
                try {
                $affiliationHistoryData['user_id'] = $affiliateUsers->{$parent_level};
                $fees_percent = isset($adminSettings[$fees_level]) ? $adminSettings[$fees_level] : '0';
                $affiliationHistoryData['amount'] = ($systemFees * $fees_percent)/ 100;
                $affiliationHistoryData['level'] = $i;
                $userWallet = get_primary_wallet($affiliationHistoryData['user_id'], 'Default');
                if (isset($userWallet)) {
                    $affiliationHistoryData['wallet_id'] = $userWallet->id;
                    $userWallet->increment('referral_balance',$affiliationHistoryData['amount']);
                }
                    BuyCoinReferralHistory::create($affiliationHistoryData);
                } catch (\Exception $e) {
                    storeException('calculateReferralFeesForBuyCoin ex',$e->getMessage());
                }
            } else {
                break;
            }
        }
        return 1;
    }

    public function myReferral()
    {
        try {
            $response = ['success' => false, 'data' => [], 'message' => __('Something went wrong')];
            $data['title'] = __('My Referral');
            $data['user'] = Auth::user();
            $data['referrals_3'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
                ->Join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
                ->Join('referral_users as ru3', 'ru3.parent_id', '=', 'ru2.user_id')
                ->join('users', 'users.id', '=', 'ru3.user_id')
                ->select('ru3.user_id as level_3', 'users.email','users.first_name as full_name','users.created_at as joining_date')
                ->get();
            $data['referrals_2'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
                ->Join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
                ->join('users', 'users.id', '=', 'ru2.user_id')
                ->select('ru2.user_id as level_2','users.email','users.first_name as full_name','users.created_at as joining_date')
                ->get();
            $data['referrals_1'] = DB::table('referral_users as ru1')->where('ru1.parent_id', Auth::user()->id)
                ->join('users', 'users.id', '=', 'ru1.user_id')
                ->select('ru1.user_id as level_1','users.email','users.first_name as full_name','users.created_at as joining_date')
                ->get();
            $referralUsers = [];

            foreach ($data['referrals_1'] as $level1) {
                $referralUser['id'] = $level1->level_1;
                $referralUser['full_name'] = $level1->full_name;
                $referralUser['email'] = $level1->email;
                $referralUser['joining_date'] = $level1->joining_date;
                $referralUser['level'] = __("Level 1");
                $referralUsers [] = $referralUser;
            }

            foreach ($data['referrals_2'] as $level2) {
                $referralUser['id'] = $level2->level_2;
                $referralUser['full_name'] = $level2->full_name;
                $referralUser['email'] = $level2->email;
                $referralUser['joining_date'] = $level2->joining_date;
                $referralUser['level'] = __("Level 2");
                $referralUsers [] = $referralUser;
            }

            foreach ($data['referrals_3'] as $level3) {
                $referralUser['id'] = $level3->level_3;
                $referralUser['full_name'] = $level3->full_name;
                $referralUser['email'] = $level3->email;
                $referralUser['joining_date'] = $level3->joining_date;
                $referralUser['level'] = __("Level 3");
                $referralUsers [] = $referralUser;
            }
            $data['referrals'] = $referralUsers;

            if (!$data['user']->Affiliate) {
                $created = $this->create($data['user']->id);
                if ($created < 1) {
                    $response = ['success' => false, 'data' => [], 'message' => __('Failed to generate new referral code.')];
                }
            }

            $data['url'] =  'ref_code=' . $data['user']->affiliate->code;

            $maxReferralLevel = 3;
            $referralQuery = $this->childrenReferralQuery($maxReferralLevel);

            $referralAll = $referralQuery['referral_all']->where('ru1.parent_id', $data['user']->id)
                ->select('ru1.parent_id', DB::raw($referralQuery['select_query']))
                ->first();

            for ($i = 0; $i < $maxReferralLevel; $i++) {
                $level = 'level' . ($i + 1);
                $data['referralLevel'] [($i + 1)] = $referralAll->{$level};
            }

            $data['select'] = 'affiliate';
            $data['max_referral_level'] = $maxReferralLevel;

            //calculate per users monthly earning from their 3 level Children
            //'level',
            $data['total_reward'] = 0;
            $monthlyEarnings = AffiliationHistory::where('user_id', $data['user']->id)
                ->where('status', STATUS_ACTIVE)
                ->get();
            if(isset($monthlyEarnings[0])) {
                foreach ($monthlyEarnings as $monthlyEarning) {
                    $data['total_reward'] = $data['total_reward'] + convert_currency($monthlyEarning->amount, "USDT", $monthlyEarning->coin_type);
                }
            }
            $tradeTransactionReferrals = TradeReferralHistory::where("user_id", $data['user']->id)->get();
            if(isset($tradeTransactionReferrals[0])) {
                foreach ($tradeTransactionReferrals as $monthlyEarning) {
                    $data['total_reward'] = $data['total_reward'] + convert_currency($monthlyEarning->amount, "USDT", $monthlyEarning->coin_type);
                }
            }

            $data['monthlyEarningHistories'] = $monthlyEarnings;
            $data['monthlyTradeEarningHistories'] = $tradeTransactionReferrals;

            $data['count_referrals'] = isset($data['referrals'][0]) ? count($data['referrals']) : 0;
            $response = ['success' => true, 'data' => $data, 'message' => __('Success')];

        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => [], 'message' => __('Something went wrong')];
        }

        return $response;
    }

}
