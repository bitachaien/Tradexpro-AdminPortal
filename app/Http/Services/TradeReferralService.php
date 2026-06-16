<?php

namespace App\Http\Services;

use App\Model\Coin;
use App\Model\ReferralUser;
use App\Model\TradeReferralHistory;
use App\Model\Wallet;
use App\Traits\ResponseFormatTrait;

class TradeReferralService
{
    use ResponseFormatTrait;

    public function sendReferralBonus($transaction)
    {
        $tradeReferralSettings = allsetting('trade_referral_settings');
        if ($tradeReferralSettings == STATUS_ACTIVE) {
            $totalAmount = $transaction->buy_fees + $transaction->sell_fees;
            $buy_user_id = $transaction->buy_user_id;
            $sell_user_id = $transaction->sell_user_id;
            $base_coin_id = $transaction->base_coin_id;
            $transaction_id = $transaction->id;
            $this->sendReferralBonusToUser($totalAmount, $buy_user_id, $base_coin_id, $transaction_id);
            $this->sendReferralBonusToUser($totalAmount, $sell_user_id, $base_coin_id, $transaction_id);
        }
    }

    public function sendReferralBonusToUser($totalAmount, $user_id, $base_coin_id, $transaction_id)
    {
        $coin_details = Coin::find($base_coin_id);
        if (isset($coin_details)) {
            $data['coin_type'] = $coin_details->coin_type;
            $data['trade_by'] = $user_id;
            $data['transaction_id'] = $transaction_id;
            $referral_user_details = ReferralUser::where('user_id', $user_id)->with('referrals.referrals')->first();

            if (isset($referral_user_details)) {
                $referral_user_one = $referral_user_details;

                $level = 1;
                $this->saveSendReferralHistory($referral_user_one, $level, $totalAmount, $base_coin_id, $data);

                if (isset($referral_user_one->referrals)) {

                    $referral_user_two = $referral_user_one->referrals;

                    $level = 2;
                    $this->saveSendReferralHistory($referral_user_two, $level, $totalAmount, $base_coin_id, $data);


                    if (isset($referral_user_two->referrals)) {
                        $referral_user_three = $referral_user_two->referrals;

                        $level = 3;
                        $this->saveSendReferralHistory($referral_user_three, $level, $totalAmount, $base_coin_id, $data);
                    }
                }
            }
            $response = ['success' => true, 'message' => __('Bonus send successfully!')];
            return $response;
        }
    }

    public function saveSendReferralHistory($referral_user_details, $level, $totalAmount, $base_coin_id, $data)
    {
        $data['user_id'] = $referral_user_details->parent_id;
        $data['child_id'] = $referral_user_details->user_id;

        $trade_fees_level = 'trade_fees_level' . $level;
        $referral_amount_percentage = allsetting($trade_fees_level);
        $referral_amount = ($totalAmount * $referral_amount_percentage) / 100;

        $user_wallet_details = Wallet::where('user_id', $referral_user_details->parent_id)
            ->where('coin_id', $base_coin_id)->first();

        if (isset($user_wallet_details)) {
            $user_wallet_details->balance += $referral_amount;
            $user_wallet_details->save();

            $data['amount'] = $referral_amount;
            $data['percentage_amount'] = $referral_amount_percentage;

            $data['level'] = $level;
            $data['wallet_id'] = $user_wallet_details->id;
            TradeReferralHistory::create($data);

            $response = ['success' => true, 'message' => __('Send Referral History is saved successfully!')];
        } else {
            $response = ['success' => false, 'message' => __('User Wallet not found!')];
        }
        return $response;
    }

    public function getAllReferralHistoryWithPaginate(int $userId, int $limit = 20, int $offset = 1, ?string $search = null): array
    {
        $referral_history_list = TradeReferralHistory::where('user_id', $userId)
            ->join('transactions', 'transactions.id', '=', 'trade_referral_histories.transaction_id')
            ->join('users as reference_user', 'reference_user.id', '=', 'trade_referral_histories.user_id')
            ->join('users as referral_user', 'referral_user.id', '=', 'trade_referral_histories.trade_by')
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('referral_user.email', 'LIKE', '%' . $search . '%')
                        ->orWhere('trade_referral_histories.transaction_id', 'LIKE', '%' . $search . '%')
                        ->orWhere('trade_referral_histories.amount', 'LIKE', '%' . $search . '%');
                });
            })
            ->select(
                'trade_referral_histories.*',
                'transactions.transaction_id',
                'reference_user.email as reference_user_email',
                'referral_user.email as referral_user_email'
            )
            ->paginate($limit, ['*'], 'page', $offset);

        return $this->responseData(true, __('Trade referral history'), $referral_history_list);
    }
}
