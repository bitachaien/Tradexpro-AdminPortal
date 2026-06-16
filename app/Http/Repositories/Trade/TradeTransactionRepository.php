<?php

namespace App\Http\Repositories\Trade;

use App\Contracts\Repositories\TradeTransactionRepositoryInterface;
use App\Dtos\CreateTransactionDto;
use App\Model\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TradeTransactionRepository implements TradeTransactionRepositoryInterface
{
    public function __construct(private Transaction $model) {}

    public function create(CreateTransactionDto $data): Transaction
    {
        return $this->model->create([
            'transaction_id' => $data->transaction_id,
            'base_coin_id' => $data->base_coin_id,
            'trade_coin_id' => $data->trade_coin_id,
            'buy_id' => $data->buy_id,
            'sell_id' => $data->sell_id,
            'buy_user_id' => $data->buy_user_id,
            'sell_user_id' => $data->sell_user_id,
            'price_order_type' => $data->price_order_type,
            'amount' => $data->amount,
            'price' => $data->price,
            'btc_rate' => $data->btc_rate,
            'total' => $data->total,
            'buy_fees' => $data->buy_fees,
            'sell_fees' => $data->sell_fees,
            'bot_order' => $data->bot_order,
            'btc' => $data->btc,
        ]);
    }

    public function findByCoinIdsAndUserId(int $baseCoinId, int $tradeCoinId, int $userId, int $limit = 20): Collection
    {
        return $this->model
            ->select(
                'transaction_id', 
                DB::raw("CASE WHEN buy_user_id =" . $userId .
                " THEN buy_fees WHEN sell_user_id =" . $userId . " THEN sell_fees END as fees"),
                DB::raw("visualNumberFormat(amount) as amount"),
                DB::raw("visualNumberFormat(price) as price"),
                DB::raw("visualNumberFormat(last_price) as last_price"),
                'price_order_type',
                DB::raw("visualNumberFormat(total) as total"),
                'created_at',
                DB::raw("TIME(created_at) as time"),
            )
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where(function($query) use ($userId) {
                $query->where('buy_user_id', $userId)
                    ->orWhere('sell_user_id', $userId);
            })
            ->limit($limit)
            ->get();
    }
}
