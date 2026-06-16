<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\ClosableOrderRepository;
use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Dtos\ClosebleOrderDto;
use App\Model\StopLimit;
use Illuminate\Support\Facades\DB;

class StopLimitRepository extends CommonRepository implements ClosableOrderRepository, StopLimitRepositoryInterface
{
    public function __construct($model)
    {
        parent::__construct($model);
    }

    public function getOrders()
    {
        return DB::select('SELECT users.email as email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, limit_price as price, amount, stop_limits.order as order_type, stop_limits.created_at FROM stop_limits join users on users.id = stop_limits.user_id join coins as base_coin_table on base_coin_id = base_coin_table.id join coins as trade_coin_table on trade_coin_id = trade_coin_table.id
            where stop_limits.status = 0'
        );
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId, $userId, $type)
    {
        if ($type == 'sell') {
            return DB::table('stop_limits')
                ->where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => '0', 'deleted_at' => null, 'order' => $type, 'is_conditioned' => 0])
                ->select(DB::raw('TRUNCATE(SUM(amount),8) as total'))
                ->get();
        } else {
            return DB::table('stop_limits')
                ->where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => '0', 'deleted_at' => null, 'order' => $type])
                ->select(DB::raw('SUM(TRUNCATE((amount)*limit_price,8)+ TRUNCATE((amount)*limit_price,8)*0.01*case when (maker_fees > taker_fees)  then (maker_fees) else (taker_fees) end ) as total'))
                ->get();
        }
    }

    public function getMyOrders($request)
    {
        $userId = $request->userId ?? getUserId();
        $user_pagination_limit = allsetting('user_pagination_limit');
        $setting_per_page = $user_pagination_limit ? $user_pagination_limit : 50;
        $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

        $result = StopLimit::leftJoin(DB::raw('coins base_coin_table'), ['base_coin_table.id' => 'stop_limits.base_coin_id'])
            ->leftJoin(DB::raw('coins trade_coin_table'), ['trade_coin_table.id' => 'stop_limits.trade_coin_id'])
            ->select(
                'trade_coin_table.coin_type as trade_coin',
                'base_coin_table.coin_type as base_coin',
                'stop_limits.limit_price as price',
                'stop_limits.order as order_type',
                'stop_limits.amount',
                'stop_limits.created_at'
            )
            ->where(['stop_limits.status' => STATUS_PENDING, 'stop_limits.user_id' => $userId])
            ->when(isset($request->search), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->orWhere('amount', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('limit_price', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('trade_coin_table.coin_type', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('base_coin_table.coin_type', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->orderBy('stop_limits.created_at', 'DESC')
            ->paginate($perPage);

        return $result;
    }

    public function getOnOrderBalanceByBaseCoinId(int $coinId, ?int $userId = null): ?string
    {
        $total = DB::table('stop_limits as stop_limits_buys')
            ->where([
                'stop_limits_buys.user_id' => $userId, 
                'stop_limits_buys.base_coin_id' => $coinId, 
                'stop_limits_buys.status' => '0', 
                'stop_limits_buys.deleted_at' => null,
                'stop_limits_buys.order' => 'buy'
            ])
            ->select(DB::raw('SUM(TRUNCATE((stop_limits_buys.amount) * stop_limits_buys.limit_price, 8) + TRUNCATE((stop_limits_buys.amount) * stop_limits_buys.limit_price, 8) * 0.01 * case when (stop_limits_buys.maker_fees > stop_limits_buys.taker_fees) then (stop_limits_buys.maker_fees) else (stop_limits_buys.taker_fees) end ) as total'))
            ->first()->total;

        return $total ? $total : '0';
    }

    public function getOnOrderBalanceByTradeCoinId(int $coinId, ?int $userId = null): ?string
    {
        $total = DB::table('stop_limits as stop_limits_sells')
            ->where([
                'stop_limits_sells.user_id' => $userId, 
                'stop_limits_sells.trade_coin_id' => $coinId, 
                'stop_limits_sells.status' => '0', 
                'stop_limits_sells.deleted_at' => null,
                'stop_limits_sells.order' => 'sell'
            ])
            ->select(DB::raw('TRUNCATE(SUM(stop_limits_sells.amount), 8) as total'))
            ->first()->total;

        return $total ? $total : '0';
    }

    public function findByIdAndLock(int $id): ?StopLimit
    {
        return $this->model
            ->where('id', $id)
            ->lockForUpdate()
            ->first();
    }

    public function findByCoinIdsAndUser(int $baseCoinId, int $tradeCoinId, int $userId): array
    {
        return $this->model
            ->select(
                'id',
                'user_id',
                'base_coin_id',
                'trade_coin_id',
                DB::raw('visualNumberFormat(TRUNCATE((stop), 8)) as stop'),
                DB::raw('visualNumberFormat(TRUNCATE((limit_price), 8)) as price'),
                DB::raw('visualNumberFormat(TRUNCATE((amount), 8)) as amount'),
                'order as type',
                DB::raw("
                visualNumberFormat(TRUNCATE(
                    CASE 
                        WHEN `order` = 'buy' THEN amount * limit_price
                        ELSE amount 
                    END, 8
                )) as total
            "),
                DB::raw('
            (CASE 
                WHEN `order` = "buy" 
                THEN 
                    (CASE 
                        WHEN maker_fees > taker_fees 
                        THEN visualNumberFormat(TRUNCATE((((amount) * limit_price) * maker_fees) * 0.01, 8))
                        ELSE visualNumberFormat(TRUNCATE((((amount) * limit_price) * taker_fees) * 0.01, 8))
                    END)
                ELSE 
                    (CASE 
                        WHEN maker_fees > taker_fees 
                        THEN visualNumberFormat(TRUNCATE((amount * maker_fees) * 0.01, 8))
                        ELSE visualNumberFormat(TRUNCATE((amount * taker_fees) * 0.01, 8))
                    END)
            END) as fees
        ')
            )->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'user_id' => $userId, 'status' => 0])
            ->get()
            ->toArray();
    }

    public function findByIdStatusUserIdAndLock(int $id, int $userId, int $status): ClosebleOrderDto
    {
        $order = $this->model
            ->where(['id' => $id, 'user_id' => $userId, 'status' => $status])
            ->lockForUpdate()
            ->first();

        return $order ? ClosebleOrderDto::fromOrderAndType(
            $order,
            'stop'
        ) : null;
    }

    public function findByIdStatusAndUserId(int $id, int $userId, int $status): ?ClosebleOrderDto
    {
        $order = $this->model
            ->where(['id' => $id, 'user_id' => $userId, 'status' => $status])
            ->first();

        return $order ? ClosebleOrderDto::fromOrderAndType(
            $order,
            'stop'
        ) : null;
    }

    public function closeOrder(int $id): void
    {
        $this->model
            ->where('id', $id)
            ->delete();
    }
}
