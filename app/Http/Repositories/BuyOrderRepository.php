<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\BuyOrderRepositoryInterface;
use App\Model\Buy;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Created by Itech.
 * User: itech
 * Date: 11/14/18
 * Time: 5:11 PM
 */

class BuyOrderRepository extends CommonRepository implements BuyOrderRepositoryInterface
{
    function __construct($model)
    {
        parent::__construct($model);
    }

    public function getAllBuyOrders(int $baseCoinId, int $tradeCoinId, int $userId, int $limit = 25)
    {
        return Buy::withTrashed()->where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'is_market' => 0])
            ->select(
                DB::raw("'buy' as type"),
                'id',
                DB::raw("visualNumberFormat(price) as price"),
                'created_at',
                DB::raw("visualNumberFormat(amount) as actual_amount"),
                'processed',
                'status',
                DB::raw("visualNumberFormat(TRUNCATE((amount) * price, 8)) as actual_total"),
                DB::raw('visualNumberFormat(TRUNCATE((amount), 8)) as amount, visualNumberFormat(TRUNCATE((amount) * price, 8)) as total,
            (case when (maker_fees > taker_fees)
             THEN
                  visualNumberFormat(TRUNCATE((((amount - processed) * price) * maker_fees) * 0.01, 8))
             ELSE
                  visualNumberFormat(TRUNCATE((((amount - processed) * price) * taker_fees) * 0.01, 8))
             END)
             as fees')
            )->orderBy('id', 'desc')->limit($limit)->get();
    }

    public function getAllOrdersForUser($base_coin_id, $trade_coin_id, $userId)
    {
        return DB::table(DB::raw('(select base_coin_id,trade_coin_id, TRUNCATE(price,8) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total, status, processed, created_at
            from buys
            where base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL
            group by base_coin_id,trade_coin_id,price
            )
            t1'))
            ->leftJoin('favourite_order_books', [
                'favourite_order_books.price' => 't1.price',
                'favourite_order_books.base_coin_id' => 't1.base_coin_id',
                'favourite_order_books.trade_coin_id' => 't1.trade_coin_id',
                //                        'favourite_order_books.user_id' => DB::raw(getUserId()),
                'favourite_order_books.user_id' =>  DB::raw(0),
                'favourite_order_books.type' => DB::raw("'buy'")
            ])
            ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from buys where user_id =' . $userId . ' and base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL group by price) t2'), ['t1.price' => 't2.price'])
            ->select('t1.created_at', 't1.status', 't1.processed', 't1.price', 't1.amount', 't1.total', 't2.amount as my_size', 'favourite_order_books.id as is_favorite')
            ->orderBy('t1.price', 'DESC');
    }

    public function getAllOrders($base_coin_id, $trade_coin_id)
    {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev') || (env('APP_ENV') == 'production')) {
            if (getUserId()) {
                $filterBuys = DB::table(DB::raw('(select base_coin_id,trade_coin_id, TRUNCATE(price,8) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total, status, processed, created_at
                                        from buys
                                        where base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL
                                        group by base_coin_id,trade_coin_id,price
                                        )
                                     t1'))
                    ->leftJoin('favourite_order_books', [
                        'favourite_order_books.price' => 't1.price',
                        'favourite_order_books.base_coin_id' => 't1.base_coin_id',
                        'favourite_order_books.trade_coin_id' => 't1.trade_coin_id',
                        //                        'favourite_order_books.user_id' => DB::raw(getUserId()),
                        'favourite_order_books.user_id' =>  DB::raw(0),
                        'favourite_order_books.type' => DB::raw("'buy'")
                    ])
                    ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from buys where user_id =' . getUserId() . ' and base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL group by price) t2'), ['t1.price' => 't2.price'])
                    ->select('t1.created_at', 't1.status', 't1.processed', 't1.price', 't1.amount', 't1.total', 't2.amount as my_size', 'favourite_order_books.id as is_favorite')
                    ->orderBy('t1.price', 'DESC');
            } else {
                $filterBuys = DB::table(DB::raw('(select base_coin_id,trade_coin_id, TRUNCATE(price,8) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total, status,processed, created_at
                                        from buys
                                        where base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL
                                        group by base_coin_id,trade_coin_id,price
                                        )
                                     t1'))
                    ->leftJoin('favourite_order_books', [
                        'favourite_order_books.price' => 't1.price',
                        'favourite_order_books.base_coin_id' => 't1.base_coin_id',
                        'favourite_order_books.trade_coin_id' => 't1.trade_coin_id',
                        'favourite_order_books.user_id' => DB::raw(0),
                        'favourite_order_books.type' => DB::raw("'buy'")
                    ])
                    ->select('t1.created_at', 't1.status', 't1.processed', 't1.price', 't1.amount', 't1.total', DB::raw('0 as my_size'), 'favourite_order_books.id as is_favorite')
                    ->orderBy('t1.price', 'DESC');
            }
        }
        return $filterBuys;
    }
    public function getOnOrderBalance($baseCoinId, $tradeCoinId, $userId)
    {
        return  DB::table('buys')
            ->where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => '0', 'is_market' => 0, 'deleted_at' => null])
            ->select(DB::raw('SUM(TRUNCATE((amount - processed)*price,8)+ TRUNCATE((amount - processed)*price,8)*0.01*case when (maker_fees > taker_fees)  then (maker_fees) else (taker_fees) end ) as total'))
            ->get();
    }

    public function getTotalAmount($baseCoinId, $tradeCoinId)
    {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
            return DB::table('buys')
                ->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => '0', 'is_market' => 0, 'deleted_at' => null])
                ->select('status', DB::raw('TRUNCATE(SUM((amount - processed)*price),8) as total'))->groupBy('status')
                ->get();
        }
    }

    public function getPrice($base_coin_id, $trade_coin_id)
    {
        return Buy::where(['base_coin_id' => $base_coin_id, 'trade_coin_id' => $trade_coin_id, 'is_market' => 0, 'status' => 0])->max('price');
    }

    public function getBuyMarketPrice($baseCoinId, $tradeCoinId, $amount)
    { //highest buy price
        try {
            $i = null;
            DB::statement(DB::raw('set @total=0'));
            $objects = DB::select('SELECT id, price, amount, processed, total_offers from (SELECT id, price, amount, processed, @total := @total + Truncate(amount-processed, 8) AS total_offers FROM buys where base_coin_id = ' . $baseCoinId . ' and trade_coin_id = ' . $tradeCoinId . ' and is_market = 0 and status=0 ORDER BY price DESC) tmp
          where tmp.total_offers < ' . $amount . ' or (tmp.total_offers >= ' . $amount . ' AND (tmp.total_offers - Truncate(amount-processed, 8)) < ' . $amount . ')');

            $price = 0.0;
            foreach ($objects as $i => $obj) {
                $price = bcaddx($obj->price, $price);
            }
            return bcdivx($price, $i + 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getMyOrders($baseCoinId, $tradeCoinId, $userId)
    {
        //        if(getUserId()) {
        if ($userId) {
            return Buy::where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => 0, 'is_market' => 0])
                ->select(
                    DB::raw("'buy' as type"),
                    'id',
                    DB::raw("visualNumberFormat(price) as price"),
                    'created_at',
                    DB::raw("visualNumberFormat(amount) as actual_amount"),
                    'processed',
                    'status',
                    DB::raw("visualNumberFormat(TRUNCATE((amount) * price, 8)) as actual_total"),
                    DB::raw('visualNumberFormat(TRUNCATE((amount - processed), 8)) as amount, visualNumberFormat(TRUNCATE((amount - processed) * price, 8)) as total,
                    (case when (maker_fees > taker_fees)
                     THEN
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * maker_fees) * 0.01, 8))
                     ELSE
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * taker_fees) * 0.01, 8))
                     END)
                     as fees')
                );
        } else {
            return Buy::where(['user_id' => 0, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => 0, 'is_market' => 0])
                ->select(DB::raw("'buy' as type"), 'id', DB::raw("visualNumberFormat(price) as price"), 'created_at', DB::raw('visualNumberFormat(TRUNCATE((amount - processed), 8)) as amount, visualNumberFormat(TRUNCATE((amount - processed) * price, 8)) as total,
                    (case when (maker_fees > taker_fees)
                     THEN
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * maker_fees) * 0.01, 8))
                     ELSE
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * taker_fees) * 0.01, 8))
                     END)
                     as fees'));
        }
    }

    public function getOrders()
    {
        return Buy::select(DB::raw("users.email as email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price,
                                                amount,processed,buys.status,visualNumberFormat(TRUNCATE((amount - processed), 8)) as remaining,
                                                is_market, buys.created_at,buys.deleted_at"))
            ->join('users', ['users.id' => 'buys.user_id'])
            ->join('coins as base_coin_table', ['base_coin_id' => 'base_coin_table.id'])
            ->join('coins as trade_coin_table', ['trade_coin_id' => 'trade_coin_table.id'])
            ->where(['request_amount' => 0, 'is_market' => 0])
            ->withTrashed();
    }

    public function getOnOrderBalanceByBaseCoinId(int $coinId, ?int $userId = null): string
    {
        $total = DB::table('buys')
            ->where([
                'buys.user_id' => $userId,
                'buys.base_coin_id' => $coinId,
                'buys.status' => '0',
                'buys.is_market' => 0,
                'buys.deleted_at' => null
            ])
            ->select(
                DB::raw('SUM(TRUNCATE((buys.amount - buys.processed) * buys.price, 8)+ TRUNCATE((buys.amount - buys.processed) * buys.price, 8) * 0.01 * case when (buys.maker_fees > buys.taker_fees)  then (buys.maker_fees) else (buys.taker_fees) end ) as total')
            )->first()->total;

        return $total ? $total : '0';
    }


    public function user($where)
    {
        return User::where($where)->first();
    }
}
