<?php

namespace App\Http\Services\DataTable;

use App\Model\Coin;
use App\Traits\NumberFormatTrait;

class CoinDataTableService
{
    use NumberFormatTrait;

    public function getData($check_module)
    {
        if (!empty($check_module) && (isset($check_module['IcoLaunchpad']) && $check_module['IcoLaunchpad'] == 'IcoLaunchpad')) {
            $coins = Coin::query()->where(function ($query) {
                return $query->where('status', '<>', STATUS_DELETED);
            });
        } else {
            $coins = Coin::where('status', '<>', STATUS_DELETED)->where(function ($query) {
                return $query->where('ico_id', '=', 0)->orWhere('is_listed', STATUS_ACTIVE);
            });
        }

        return datatables()->of($coins)
            ->addColumn('created_at', function ($item) {
                return $item->created_at;
            })
            ->addColumn('currency_type', function ($item) {
                return getTradeCurrencyType($item->currency_type);
            })
            ->addColumn('network', function ($item) {
                return $item->currency_type == CURRENCY_TYPE_CRYPTO ? api_settings_new($item->network) : __("Fiat Currency");
            })
            ->addColumn('coin_price', function ($item) {
                return $this->truncate_num($item->coin_price) . '</br> USD/' . $item->coin_type;
            })
            ->addColumn('status', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.ico_switch', $data);
            })
            ->addColumn('is_demo_trade', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.demo_switch', $data);
            })
            ->addColumn('actions', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.actions', $data);
            })
            ->rawColumns(['network', 'coin_price', 'status', 'is_demo_trade', 'actions'])
            ->make(true);
    }
}
