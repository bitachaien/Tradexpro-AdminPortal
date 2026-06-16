<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\TradableOrderRepositoryInterface;
use App\Dtos\ClosebleOrderDto;
use App\Dtos\OrderCreationDto;
use App\Model\Buy;
use App\Model\Sell;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderRepository implements TradableOrderRepositoryInterface
{
    public function __construct(private Buy|Sell $model)
    {
    }

    public function create(OrderCreationDto $data): Buy|Sell
    {
        return $this->model->create([
            'user_id' => $data->user_id,
            'trade_coin_id' => $data->trade_coin_id,
            'base_coin_id' => $data->base_coin_id,
            'amount' => truncate_num($data->amount),
            'processed' => $data->processed,
            'virtual_amount' => $data->virtual_amount,
            'price' => $data->price,
            'btc_rate' => $data->btc_rate,
            'is_market' => $data->is_market,
            'category' => $data->category,
            'maker_fees' => $data->maker_fees,
            'taker_fees' => $data->taker_fees,
            'is_conditioned' => $data->is_conditioned,
        ]);
    }

    public function getById(int $id): Buy|Sell|null
    {
        return $this->model->find($id);
    }

    public function findByIdAndLock(int $id): Buy|Sell|null
    {
        return $this->model->lockForUpdate()->find($id);
    }

    public function closeOrder(int $id): void
    {
        $this->model->where(['id' => $id, 'status' => 0])
            ->update(['amount' => DB::raw('processed'), 'status' => 1]);
    }

    public function findByIdStatusUserIdAndLock(int $id, int $userId, int $status): ?ClosebleOrderDto
    {
        $order = $this->model
            ->where(['id' => $id, 'user_id' => $userId, 'status' => $status])
            ->lockForUpdate()
            ->first();
        
        return $order ? ClosebleOrderDto::fromOrderAndType(
            $order,
            strtolower(Str::singular($this->model->getTable()))
        ) : null;
    }

    public function findByIdStatusAndUserId(int $id, int $userId, int $status): ?ClosebleOrderDto
    {
        $order = $this->model
            ->where(['id' => $id, 'user_id' => $userId, 'status' => $status])
            ->first();

        return $order ? ClosebleOrderDto::fromOrderAndType(
            $order,
            strtolower(Str::singular($this->model->getTable()))
        ) : null;
    }

    public function hasMoreCountThanLimit(int $baseCoinId, int $tradeCoinId, int $limit, bool $isBot): bool
    {
        return $this->model
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where('is_bot', $isBot ? 1 : 0)
            ->limit($limit)
            ->get()
            ->count() >= $limit;
    }

    public function closeLastBotOrder(int $baseCoinId, int $tradeCoinId, string $orderDir): void
    {
        if ($orderDir != 'asc' && $orderDir != 'desc') {
            throw new \InvalidArgumentException('Invalid order by direction. Expected "asc" or "desc".');
        }

        $this->model
            ->orderBy('price', $orderDir)
            ->orderBy('id', 'asc')
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where('is_bot', 1)
            ->where('status', 0)
            ->limit(1)
            ->update([
                'status' => 1,
                'amount' => DB::raw('processed'),
            ]);
    }

    public function deleteBotOrders(int $baseCoinId, int $tradeCoinId, int $superAdminId): void
    {
        $transactionColumn = $this->model instanceof Buy ? 'buy_id' : 'sell_id';

        $this->model
            ->where('user_id', $superAdminId)
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where('status', 1)
            ->where('is_bot', 1)
            ->whereNotIn('id', function ($query) use ($transactionColumn) {
                $query->select($transactionColumn)
                    ->from('transactions');
            })->forceDelete();
    }

    public function getTradableOrders(int $baseCoinId, int $tradeCoinId, int $limit = 0): Collection
    {
        $query = $this->model
            ->where('base_coin_id', $baseCoinId)
            ->where('trade_coin_id', $tradeCoinId)
            ->where('status', 0)
            ->where('is_market', 0);

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getMatchedOrders(
        int $baseCoinId,
        int $tradeCoinId,
        int $isMarket,
        $price,
        string $orderByDirection,
        string $priceComparator
    ): Generator
    {
        do {
            $query = $this->model
                ->where('base_coin_id', $baseCoinId)
                ->where('trade_coin_id', $tradeCoinId)
                ->where('status', 0)
                ->where('is_market', 0);
            
            if($isMarket == 0) {
                $query->where('price', $priceComparator, $price);
            }

            $orders = $query->orderBy('price', $orderByDirection)->limit(100)->get();

            if($orders->isEmpty()) {
                break;
            }

            yield $orders;
        } while(true);
    }
}
