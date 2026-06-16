<?php

/**
 * Created by PhpStorm.
 * User: wasim
 * Date: 11/28/18
 * Time: 5:44 PM
 */

namespace App\Http\Services;


use App\Jobs\EmailInvitationSendJob;
use App\Jobs\TradingAffiliationBonusDeposit;
use App\Model\AffiliationCode;
use App\Model\AffiliationHistory;
use App\Models\BetByBetAffiliationHistory;
use App\Models\CardAffiliationHistory;
use App\Models\Coin;
use App\Models\ReferralUser;
use App\Models\TradingAffiliationHistory;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\ResponseFormatTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AffiliationService
{
    use ResponseFormatTrait;

    public function __construct()
    {
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect(); //important as the existing connection if any would be in strict mode
    }

    public function __destruct()
    {
        config()->set('database.connections.mysql.strict', true);
        DB::reconnect();
    }


    public function create($user)
    {
        $affiliateCodeInput['user_id'] = $user->id;
        $affiliateCodeInput['code'] = uniqid($user->id);
        $affiliateCodeInput['status'] = 1;
        //here checking the exception for duplicate key(code),
        // if duplication found, then re-run this function again from exception

        try {
            $created['id'] = AffiliationCode::create($affiliateCodeInput)->id;
            $created['code'] = $affiliateCodeInput['code'];
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == '1062') {
                $this->create($user->id);
            }
        }

        return $created;
    }

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

    public function firtLevelReferralUsers($user_id, $level)
    {
        $data = ['status' => true, 'data' => [], 'message' => 'referral.children.list'];

        if ($level == null) {
            $level = 0;
        } else {
            $level++;
        }
        $id = $user_id;
        try {
            $id = decrypt($id);
        } catch (\Exception $e) {
            return null;
        }

        $maxReferralLevel = trade_max_level();

        $referralAll = DB::table('referral_users AS ru1')
            ->leftJoin('referral_users AS ru2', 'ru1.user_id', '=', 'ru2.parent_id')
            ->leftJoin('users', 'users.id', '=', 'ru1.user_id')
            ->leftJoin('user_informations', 'users.id', '=', 'user_informations.user_id')
            ->where('ru1.parent_id', $id)
            ->select('ru1.user_id as key', 'users.email as name', 'user_informations.avatar as icon', DB::raw('COUNT(DISTINCT(ru2.id)) as children_count'))
            ->groupBy('key')
            ->get();

        $referralAll->each(function ($item, $key) use ($level, $maxReferralLevel) {
            $item->key = encrypt($item->key);
            if ($level > 2) {
                $item->name = $this->formatEmail($item->name);
            }

            if ($item->icon != null && file_exists(public_path($item->icon))) {
                $item->icon = getImageUrl(path_user_image_upload() . $item->icon);
            } else {
                $item->icon = asset('assets/frontend/images/profile/avatar.jpg');
            }

            //            $data['level'] = $level;
            //
            //            $item->data = $data;

            $item->level = $level;
            if ($item->children_count > 0 && $level <= $maxReferralLevel) {
                $item->isLeaf = false;
            } else {
                $item->isLeaf = true;
            }
        });

        $data['data']['referral_children'] = $referralAll;
        return $data;
    }

    public function formatEmail($email)
    {
        $em = explode("@", $email);
        $name = implode(array_slice($em, 0, count($em) - 1), '@');
        if (strlen($name) > 3) {
            $len = floor(strlen($name) - 3);
        } else {
            $len = 0;
        }

        return substr($name, 0, 3) . str_repeat('*', $len) . "@" . end($em);
    }

    public function getReferralUrl($user)
    {
        $data = ['status' => true, 'data' => [], 'message' => 'referral.url.'];

        $code = null;
        if (!$user->affiliationCode) {
            $created = $this->create($user);
            if ($created['id'] < 1) {
                $data['message'] = 'failed.to.generate.new.referral.code';
                $data['status'] = false;
            } else {
                $code = $created['code'];
            }
        } else {
            $code = $user->affiliationCode->code;
        }

        if ($code != null) {
            $url = env('WEB_URL') . '/sign-up?referral_code=' . $code;
            $data['data']['referral_url'] = $url;
        }


        return $data;
    }

    public function addParentReferral($parentReferralData, $user)
    {
        $data = ['status' => false, 'data' => [], 'message' => 'invalid.referral.url'];

        $referralUrl = $parentReferralData['ref_url'];
        $urlParse = parse_url($referralUrl);
        $getReferralCode = $this->getReferralCodeFromUrl($urlParse);
        $referral_code = null;
        if ($getReferralCode['status']) {
            $referral_code = $getReferralCode['referral_code'];
        }

        if ($referral_code) {
            $parentData = AffiliationCode::where('code', $referral_code)->first();
            $userExist = ReferralUser::where('user_id', $user->id)->first();
        }
        //        dd($referral_code);

        if (!empty($userExist)) {
            $data['message'] = 'you.are.already.referred.by.someone';
        } elseif (!empty($parentData) && $parentData->user_id == $user->id) {
            $data['message'] = 'you.cant.become.your.own.parent';
        } else {
            $parentId = $parentData->user_id;
            $referralUserdata['user_id'] = $user->id;
            $referralUserdata['parent_id'] = $parentId;

            $directChild = ReferralUser::where('user_id', $parentId)
                ->where('parent_id', $user->id)
                ->first();
            if ($directChild) {
                $data['message'] = 'your.child.cant.become.your.parent';
            } else {
                $created = ReferralUser::create($referralUserdata);
                $data['message'] = 'referral.parent.has.been.saved.successfully';
                $data['status'] = true;
            }
        }

        return $data;
    }

    private function getReferralCodeFromUrl($urlParse)
    {
        if (!isset($urlParse['query'])) {
            return ['status' => false];
        }

        $values = explode('=', $urlParse['query']);
        $getRef = substr($urlParse['query'], 0, 13);
        if ($getRef !== 'referral_code') {
            return ['status' => false];
        }
        if (!isset($values[1]) || $values[1] == '') {
            return ['status' => false];
        }

        return ['status' => true, 'referral_code' => $values[1]];
    }

    public function getReferralParent($user)
    {
        $data = ['status' => false, 'data' => [], 'message' => 'user.doesnt.have.any.parent.referral'];

        $parentReferral = ReferralUser::join('users', 'referral_users.parent_id', '=', 'users.id')->where('user_id', $user->id)->first();
        if ($parentReferral) {
            $data['status'] = true;
            $data['data']['parent_referral_user'] = $parentReferral->email;
            $data['message'] = 'parent.referral';
        }

        return $data;
    }

    public function emailInvitation($emailInvitationData, $user)
    {
        $data = ['status' => false, 'data' => [], 'message' => 'failed.to.send.email.invitation'];
        try {
            $emailList = $emailInvitationData['email_list'];
            $emailInvitation['email_list'] = $emailList;
            $emailInvitation['invalid_email'] = [];
            $emailInvitation['valid_email'] = [];
            foreach ($emailInvitation['email_list'] as $email) {
                if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                    $emailInvitation['invalid_email'][] = trim($email);
                } else {
                    if (trim($email) != $user->email) {
                        $emailInvitation['valid_email'][] = trim($email);
                    } else {
                        $data['message'] = 'you.can\'t.send.email.to.your.own.account';
                        $data['status'] = false;
                    }
                }
            }
            $userReferral = $this->getReferralUrl($user);
            $emailInvitation['url'] = $userReferral['data']['referral_url'];
            if (sizeof($emailInvitation['valid_email']) > 0) {
                dispatch(new EmailInvitationSendJob($emailInvitation))->onQueue('email-invitation');
                $data['message'] = 'email.invitation.sent';
                $data['status'] = true;
            }

            $data['data']['email_invitation'] = $emailInvitation;
        } catch (\Exception $e) {
        }

        return $data;
    }

    public function getReferralEarning($user)
    {
        $data = ['status' => true, 'data' => [], 'message' => 'referral.earning'];

        $monthlyEarnings = TradingAffiliationHistory::select(
            DB::raw('DATE_FORMAT(`created_at`,\'%Y-%m\') as "year_month"'),
            DB::raw('SUM(amount) AS total_amount'),
            DB::raw('COUNT(DISTINCT(child_id)) AS total_child')
        )
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->groupBy('year_month')
            ->get();
        //dd($monthlyEarnings);
        $tradingMonthlyEarningData = [];
        foreach ($monthlyEarnings as $monthlyEarning) {
            $tradingMonthlyEarningData[$monthlyEarning->year_month]['year_month'] = $monthlyEarning->year_month;
            $tradingMonthlyEarningData[$monthlyEarning->year_month]['trading_total_amount'] = $monthlyEarning->total_amount;
        }

        $betbybetMonthlyEarnings = BetByBetAffiliationHistory::select(
            DB::raw('DATE_FORMAT(`referral_month`,\'%Y-%m\') as "year_month"'),
            DB::raw('SUM(amount) AS total_amount'),
            DB::raw('COUNT(DISTINCT(child_id)) AS total_child')
        )
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->groupBy('year_month')
            ->get();

        $betbybetMonthlyEarningData = [];
        foreach ($betbybetMonthlyEarnings as $monthlyEarning) {
            $betbybetMonthlyEarningData[$monthlyEarning->year_month]['year_month'] = $monthlyEarning->year_month;
            $betbybetMonthlyEarningData[$monthlyEarning->year_month]['betting_total_amount'] = $monthlyEarning->total_amount;
        }

        $cardMonthlyEarnings = CardAffiliationHistory::select(
            DB::raw('DATE_FORMAT(`created_at`,\'%Y-%m\') as "year_month"'),
            DB::raw('SUM(amount) AS card_total_amount'),
            DB::raw('COUNT(DISTINCT(child_id)) AS total_child')
        )
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->groupBy('year_month')
            ->get();

        $cardMonthlyEarningData = [];
        foreach ($cardMonthlyEarnings as $cardMonthlyEarning) {
            $cardMonthlyEarningData[$cardMonthlyEarning->year_month]['year_month'] = $cardMonthlyEarning->year_month;
            $cardMonthlyEarningData[$cardMonthlyEarning->year_month]['card_total_amount'] = $cardMonthlyEarning->card_total_amount;
        }
        $betbybetKeys = array_flip(array_keys($betbybetMonthlyEarningData));
        $tradingKeys = array_flip(array_keys($tradingMonthlyEarningData));
        $cardKeys = array_flip(array_keys($cardMonthlyEarningData));
        $monthArray = array_merge($betbybetKeys, $tradingKeys);
        $monthArray = array_merge($monthArray, $cardKeys);
        ksort($monthArray);
        $monthArray = array_reverse($monthArray);
        $formatedEarningData = [];
        $totalEarning = "0.00";
        foreach ($monthArray as $key => $month) {
            $monthlyEarningData['month'] = date('M Y', strtotime($key));
            $monthlyEarningData['trading_commission'] = isset($tradingMonthlyEarningData[$key]) ? truncate_num($tradingMonthlyEarningData[$key]['trading_total_amount']) : "0.00";
            $monthlyEarningData['batting_commission'] = isset($betbybetMonthlyEarningData[$key]) ? truncate_num($betbybetMonthlyEarningData[$key]['betting_total_amount']) : "0.00";
            $monthlyEarningData['card_pre_order_commission'] = isset($cardMonthlyEarningData[$key]) ? truncate_num($cardMonthlyEarningData[$key]['card_total_amount']) : "0.00";
            $monthlyEarningData['total'] = truncate_num($monthlyEarningData['trading_commission'] + $monthlyEarningData['batting_commission'] + $monthlyEarningData['card_pre_order_commission']);
            $totalEarning += $monthlyEarningData['total'];
            $formatedEarningData[] = $monthlyEarningData;
        }
        $data['data']['total_earning'] = truncate_num($totalEarning);
        $data['data']['affiliation_earning_histories'] = $formatedEarningData;
        $data['data']['coin_type'] = env("AFFILIATION_BONUS_COIN_TYPE", "AMZ");

        return $data;
    }

    public function getReferralCount($user)
    {
        $data = ['status' => true, 'data' => [], 'message' => 'referral.children.count'];

        $maxReferralLevel = trade_max_level();
        $referralQuery = $this->childrenReferralQuery($maxReferralLevel);

        $referralAll = $referralQuery['referral_all']->where('ru1.parent_id', $user->id)
            ->select('ru1.parent_id', DB::raw($referralQuery['select_query']))
            ->first();
        $data['data']['referral_level_with_children_count'] = [];
        for ($i = 0; $i < $maxReferralLevel; $i++) {
            $level = 'level' . ($i + 1);
            $referral_level_with_children_count['level'] = __('level_') . " " . ($i + 1);
            $referral_level_with_children_count['count'] = $referralAll->{$level};
            $data['data']['referral_level_with_children_count'][] = $referral_level_with_children_count;
        }
        $data['data']['max_referral_level'] = $maxReferralLevel;

        return $data;
    }

    public function storeAffiliationHistory($transaction = null)
    {
        if ($transaction != null) {
            $adminSettings = $this->checkAdminSettings();
            $buyUser = $transaction->buy_user_id;
            $sellUser = $transaction->sell_user_id;
            $transactionId = $transaction->id;
            $maxReferralLevel = trade_max_level();
            try {

                $buyAffiliation = $this->parentReferrals($maxReferralLevel, $buyUser);
                if (!empty($buyAffiliation)) {
                    $coin = Coin::where('id', $transaction->base_coin_id)->first();
                    $coinRate = getPerCoinRate("AMZ", $coin->coin_type);
                    $this->calculateReferralFees($adminSettings, $transactionId, $buyAffiliation, $transaction->buy_fees, $coinRate, 1, $maxReferralLevel);
                }
            } catch (\Exception $e) {

                storeException('storeAffiliationHistory', $e->getMessage());
                return false;
            }
            try {
                $sellAffiliation = $this->parentReferrals($maxReferralLevel, $sellUser);

                if ($sellAffiliation) {
                    $coin = Coin::where('id', $transaction->base_coin_id)->first();
                    $coinRate = getPerCoinRate("AMZ", $coin->coin_type);
                    $this->calculateReferralFees($adminSettings, $transactionId, $sellAffiliation, $transaction->sell_fees, $coinRate, 2, $maxReferralLevel);
                }
            } catch (\Exception $e) {
                storeException('exx', $e->getMessage());
                return false;
            }
            return true;
        }

        return 1;
    }

    public function checkAdminSettings()
    {
        $adminSettings = allsetting(['trading_fees_level1', 'trading_fees_level2', 'trading_fees_level3', 'trading_fees_level4', 'trading_fees_level5', 'trading_fees_level6', 'trading_fees_level7', 'trading_fees_level8', 'trading_fees_level9', 'trading_fees_level10']);
        if (empty($adminSettings['trading_fees_level1'])) {
            $adminSettings['trading_fees_level1'] = 10;
        }
        if (empty($adminSettings['trading_fees_level2'])) {
            $adminSettings['trading_fees_level2'] = 5;
        }
        if (empty($adminSettings['trading_fees_level3'])) {
            $adminSettings['trading_fees_level3'] = 10;
        }
        if (empty($adminSettings['trading_fees_level4'])) {
            $adminSettings['trading_fees_level4'] = 0;
        }
        if (empty($adminSettings['trading_fees_level5'])) {
            $adminSettings['trading_fees_level5'] = 0;
        }
        if (empty($adminSettings['trading_fees_level6'])) {
            $adminSettings['trading_fees_level6'] = 0;
        }
        if (empty($adminSettings['trading_fees_level7'])) {
            $adminSettings['trading_fees_level7'] = 0;
        }
        if (empty($adminSettings['trading_fees_level8'])) {
            $adminSettings['trading_fees_level8'] = 0;
        }
        if (empty($adminSettings['trading_fees_level9'])) {
            $adminSettings['trading_fees_level9'] = 0;
        }
        if (empty($adminSettings['trading_fees_level10'])) {
            $adminSettings['trading_fees_level10'] = 0;
        }
        return $adminSettings;
    }


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

    protected function calculateReferralFees($adminSettings, $transactionId, $affiliateUsers, $systemFees, $coinRate, $orderType, $maxReferralLevel = 1)
    {
        try {
            $systemFees = bcdivx($systemFees, $coinRate);
        } catch (\Exception $e) {
            storeException('calculateReferralFees', $e->getMessage());
            return 1;
        }

        $affiliationHistoryData['system_fees'] = $systemFees;
        $affiliationHistoryData['child_id'] = $affiliateUsers->user_id;
        $affiliationHistoryData['status'] = 0;
        $affiliationHistoryData['transaction_id'] = $transactionId;
        $affiliationHistoryData['order_type'] = $orderType;


        for ($i = 1; $i <= $maxReferralLevel; $i++) {
            $parent_level = 'parent_level_user_' . $i;
            $fees_level = 'trading_fees_level' . $i;
            if ($affiliateUsers->{$parent_level}) {
                $affiliationHistoryData['user_id'] = $affiliateUsers->{$parent_level};
                $fees_percent = isset($adminSettings[$fees_level]) ? $adminSettings[$fees_level] : '0';
                $affiliationHistoryData['amount'] = bcdivx(bcmulx($systemFees, $fees_percent), 100);
                $affiliationHistoryData['level'] = $i;
                try {
                    TradingAffiliationHistory::create($affiliationHistoryData);
                } catch (\Exception $e) {
                    storeException('calculateReferralFees ex', $e->getMessage());
                }
            } else {
                break;
            }
        }
        return 1;
    }


    public function depositTradingAffiliationFees()
    {

        if (in_array(config('app.env'), ['stg', 'local'])) {
            $firstDay = $start = Carbon::now();
        } else {
            $firstDay = $start = Carbon::now()->startOfMonth();
        }
        echo $firstDay;
        $id = 0;
        while (true) {
            $affiliateUser = User::join('trading_affiliation_histories', 'users.id', '=', 'trading_affiliation_histories.user_id')
                ->where('users.id', '>', $id)
                ->where('trading_affiliation_histories.created_at', '<', $firstDay)
                ->where('trading_affiliation_histories.status', 0)
                ->select('users.id')
                ->orderBy('users.id', 'asc')
                ->first();

            if ($affiliateUser) {
                $userWallet = UserWallet::where('user_id', $affiliateUser->id)
                    ->join('coins', 'coins.id', '=', 'user_wallets.coin_id')
                    ->where('coins.coin_type', 'AMZ')
                    ->select('user_wallets.*')
                    ->first();
                dispatch(new TradingAffiliationBonusDeposit($userWallet))->onQueue('trading-affiliation-bonus-deposit');
                $id = $affiliateUser->id;
            } else {
                break;
            }
        }
    }


    public function affiliationMonthlyData()
    {
        $monthlyReferrals = TradingAffiliationHistory::select(
            DB::raw('DATE_FORMAT(created_at,\'%Y-%m\') AS "year_month"'),
            DB::raw('SUM(amount) AS total_amount'),
            DB::raw('COUNT(DISTINCT user_id) AS total_users')
        )
            //            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->groupBy(DB::raw('DATE_FORMAT(created_at,\'%Y-%m\')'))
            ->get();


        $buyTransactionFees = TradingAffiliationHistory::where('trading_affiliation_histories.order_type', 1)
            ->where('trading_affiliation_histories.level', 1)
            ->select(DB::raw('SUM(trading_affiliation_histories.system_fees) AS total_fees'), DB::raw('DATE_FORMAT(trading_affiliation_histories.created_at,\'%Y-%m\') AS "year_month"'))
            ->groupBy(DB::raw('DATE_FORMAT(trading_affiliation_histories.created_at,\'%Y-%m\')'))
            ->pluck('total_fees', 'year_month')->toArray();


        $sellTransactionFees = TradingAffiliationHistory::where('trading_affiliation_histories.order_type', 2)
            ->where('trading_affiliation_histories.level', 1)
            ->select(DB::raw('SUM(trading_affiliation_histories.system_fees) AS total_fees'), DB::raw('DATE_FORMAT(trading_affiliation_histories.created_at,\'%Y-%m\') AS "year_month"'))
            ->groupBy(DB::raw('DATE_FORMAT(trading_affiliation_histories.created_at,\'%Y-%m\')'))
            ->pluck('total_fees', 'year_month')->toArray();

        $monthlyReferralsBonusData = [];


        foreach ($monthlyReferrals as $monthlyReferral) {
            $transactionFees = "0";
            if (isset($buyTransactionFees[$monthlyReferral->year_month])) {
                $transactionFees = bcaddx($buyTransactionFees[$monthlyReferral->year_month], $transactionFees);
            }
            if (isset($sellTransactionFees[$monthlyReferral->year_month])) {
                $transactionFees = bcaddx($sellTransactionFees[$monthlyReferral->year_month], $transactionFees);
            }

            $monthlyReferralsBonusData[$monthlyReferral->year_month]['total_users'] = $monthlyReferral->total_users;
            $monthlyReferralsBonusData[$monthlyReferral->year_month]['total_amount'] = $monthlyReferral->total_amount;
            $monthlyReferralsBonusData[$monthlyReferral->year_month]['transaction_fees'] = $transactionFees;
            $monthlyReferralsBonusData[$monthlyReferral->year_month]['percentage'] = bcmulx(bcdivx($monthlyReferralsBonusData[$monthlyReferral->year_month]['total_amount'], $transactionFees), "100");
        }

        $affiliationKeys = array_flip(array_keys($monthlyReferralsBonusData));
        ksort($monthArray);
        $monthArray = array_reverse($monthArray);

        $thisMonth = Carbon::now()->startOfMonth();
        $thisMonth = date('Y-m', strtotime($thisMonth));

        $startDate = Carbon::today()->subDays(30);
        $endDate = Carbon::now();

        $data['monthlyReferralBonusHistories'] = $monthlyReferralsBonusData;
        $data['betbybetMonthlyReferralsBonusHistories'] = 0;
        $data['buyTransactionFees'] = $buyTransactionFees;
        $data['sellTransactionFees'] = $sellTransactionFees;
        $data['monthArray'] = $monthArray;
        $data['thisMonth'] = $thisMonth;
        $data['startDate'] = $startDate->format('Y-m-d');
        $data['endDate'] = $endDate->format('Y-m-d');
        $data['pagetitle'] = __('Referral Report');

        return $data;
    }

    public function getWithdrawalReferralHistoryWithPaginate(int $userId, int $limit = 20, int $offset = 1, ?string $search = null): array
    {
        $referral_history_list = AffiliationHistory::where('user_id', $userId)
            ->join('users as reference_user', 'reference_user.id', '=', 'affiliation_histories.user_id')
            ->join('users as referral_user', 'referral_user.id', '=', 'affiliation_histories.child_id')
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('referral_user.email', 'LIKE', '%' . $search . '%')
                        ->orWhere('transaction_id', 'LIKE', '%' . $search . '%')
                        ->orWhere('amount', 'LIKE', '%' . $search . '%');
                });
            })
            ->select(
                'affiliation_histories.*',
                'reference_user.email as reference_user_email',
                'referral_user.email as referral_user_email'
            )
            ->orderBy('affiliation_histories.created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $offset);

        return $this->responseData(true, __('Withdrawal referral history'), $referral_history_list);
    }
}
