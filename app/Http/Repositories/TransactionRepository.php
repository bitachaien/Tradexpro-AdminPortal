<?php

namespace App\Http\Repositories;


use App\Model\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends CommonRepository
{
    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getOrders()
    {
        return DB::select(
            "SELECT buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transaction_id, remove_from_chart, transactions.created_at FROM transactions
              join users as buy_user on buy_user.id = transactions.buy_user_id
              join users as sell_user on sell_user.id = transactions.sell_user_id
              join coins as base_coin_table on base_coin_id = base_coin_table.id
              join coins as trade_coin_table on trade_coin_id = trade_coin_table.id"
        );
    }

    public function getOrdersQuery()
    {
        return DB::table('transactions')
            ->select(DB::raw("buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transaction_id, remove_from_chart, transactions.created_at"))
            ->join(DB::raw("users as buy_user"), "buy_user.id", "=", "transactions.buy_user_id")
            ->join(DB::raw("users as sell_user"), "sell_user.id", "=", "transactions.sell_user_id")
            ->join(DB::raw("coins as base_coin_table"), "base_coin_id", "=", "base_coin_table.id")
            ->join(DB::raw("coins as trade_coin_table"), "trade_coin_id", "=", "trade_coin_table.id");
    }


    public function getMyTradeHistory($select, $where, $orWhere = null, $duration = null)
    {
        return Transaction::select($select)->where($where)->orWhere(function ($query) use ($orWhere) {
            $query->where($orWhere);
        })->where('created_at', '>=', $duration)->orderBy('id', 'DESC');
    }

    public function getMyAllTradeHistory($select, $where, $orWhere = null, $order_data)
    {
        return Transaction::join('coins as bc', 'bc.id', '=', 'base_coin_id')
            ->join('coins as tc', 'tc.id', '=', 'trade_coin_id')
            ->select($select)
            ->where(function ($query) use ($where, $orWhere) {
                $query->where($where);
                if ($orWhere) {
                    $query->orWhere($orWhere);
                }
            })
            ->when(isset($order_data['search']), function ($query) use ($order_data) {
                $query->where(function ($q) use ($order_data) {
                    $search = '%' . $order_data['search'] . '%';
                    $q->where('amount', 'LIKE', $search)
                        ->orWhere('price', 'LIKE', $search)
                        ->orWhere('transaction_id', 'LIKE', $search)
                        ->orWhere('bc.coin_type', 'LIKE', $search)
                        ->orWhere('tc.coin_type', 'LIKE', $search);
                });
            })
            ->orderBy(
                $order_data['column_name'] ?? 'transactions.id',
                $order_data['order_by'] ?? 'DESC'
            );
    }

    public function getAllTradeHistory($where)
    {
        return Transaction::select(
            DB::raw("visualNumberFormat(amount) as amount"),
            DB::raw("visualNumberFormat(price) as price"),
            DB::raw("visualNumberFormat(last_price) as last_price"),
            DB::raw("visualNumberFormat(total) as total"),
            'price_order_type',
            'created_at as time',
        )->where($where)->orderBy('id', 'DESC');
    }
    public function getLastTrade($where)
    {
        return Transaction::select(
            DB::raw("visualNumberFormat(amount) as amount"),
            DB::raw("visualNumberFormat(price) as price"),
            DB::raw("visualNumberFormat(last_price) as last_price"),
            'price_order_type',
            DB::raw("visualNumberFormat(total) as total"),
            DB::raw("created_at as time")
        )->where($where)->orderBy('id', 'DESC')->first();
    }

    public function getLastTradeHistory($where)
    {
        return Transaction::select(DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"))->where($where)->orderBy('id', 'DESC');
    }

    public function getOrdersQueryReport($transaction_with = 'all')
    {
        $query = DB::table('transactions')
            ->select(DB::raw("buy_user.email as buy_user_email, sell_user.email as sell_user_email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price, amount, total, transaction_id, remove_from_chart, transactions.created_at"))
            ->join(DB::raw("users as buy_user"), "buy_user.id", "=", "transactions.buy_user_id")
            ->join(DB::raw("users as sell_user"), "sell_user.id", "=", "transactions.sell_user_id")
            ->join(DB::raw("coins as base_coin_table"), "base_coin_id", "=", "base_coin_table.id")
            ->join(DB::raw("coins as trade_coin_table"), "trade_coin_id", "=", "trade_coin_table.id");

        $bot = get_super_admin_id();

        if ($transaction_with == TRANSACTION_FILTER_BOT_TO_BOT) {
            $query = $query->where("buy_user.id", $bot)->where("sell_user.id", $bot);
        } else if ($transaction_with == TRANSACTION_FILTER_BOT_TO_USER) {
            $query = $query->where(function ($q) use ($bot) {
                return $q->where(function ($qq) use ($bot) {
                    return $qq->where("buy_user.id", $bot)->where("sell_user.id", "<>", $bot);
                })
                    ->orWhere(function ($qq) use ($bot) {
                        return $qq->where("sell_user.id", $bot)->where("buy_user.id", "<>", $bot);
                    });
            });
        } else if ($transaction_with == TRANSACTION_FILTER_USER_TO_USER) {
            $query = $query->where(function ($q) use ($bot) {
                return $q->where("buy_user.id", "<>", $bot)->where("sell_user.id", "<>", $bot);
            });
        }

        return $query;
    }

    public function getLatestTransactionIds(
        int $baseCoinId,
        int $tradeCoinId,
        int $limit = 20
    ): array | null
    {
        return $this->model
            ->orderBy('id', 'desc')
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }

    public function removeBotTransactions(
        array $idsToKeep,
        int $maxDeletableId,
        int $baseCoinId,
        int $tradeCoinId,
        int $superAdminId
    ): void
    {
        $this->model
            ->where('id', '<', $maxDeletableId)
            ->whereNotIn('id', $idsToKeep)
            ->where('buy_user_id', $superAdminId)
            ->where('sell_user_id', $superAdminId)
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->forceDelete();
    }

    public function getLastTransactionIdBeforeHours(int $baseCoinId, int $tradeCoinId, int $hours): array 
    {
        return $this->model
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where('created_at', '>=' , Carbon::now()->subHours($hours))
            ->orderBy('id', 'desc')
            ->limit(1)
            ->pluck('id')
            ->toArray();
    }
}
