<?php

namespace App\Http\Controllers\admin;

use App\Model\FutureWallet;
use Illuminate\Http\Request;
use App\Model\FutureTradeLongShort;
use App\Http\Controllers\Controller;
use App\Http\Services\FutureTradeService;
use App\Model\FutureTradeTransactionHistory;
use App\Model\FutureTradeBalanceTransferHistory;

class FutureTradeController extends Controller
{
    private $futureTradeService;

    public function __construct()
    {
        $this->futureTradeService = new FutureTradeService;
    }
    public function dashboard()
    {
        return view('admin.future-trade.dashboard.index');
    }

    public function walletList(Request $request)
    {
        $data['title'] = __('Future Trade User Wallet List');

        $wallet_list = FutureWallet::with(['user']);

        if ($request->ajax()) {

            $data['items'] = $wallet_list;
            return datatables($data['items'])
                ->addColumn('user_name', function ($query) {
                    return $query->user_name = isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : __('N/A');
                })
                ->addColumn('user_email', function ($query) {
                    return $query->user_name = isset($query->user) ? $query->user->email : __('N/A');
                })
                ->editColumn('created_at', function ($query) {
                    return $query->created_at;
                })->make(true);
        }
        return view('admin.future-trade.user.wallet-list', $data);
    }

    public function transferHistoryList(Request $request)
    {
        $data['title'] = __('Future Trade User Wallet Transfer History');

        $wallet_list = FutureTradeBalanceTransferHistory::with(['user', 'spot_wallet', 'future_wallet'])->latest();

        if ($request->ajax()) {

            $data['items'] = $wallet_list;
            return datatables($data['items'])
                ->addColumn('user_name', function ($query) {
                    return $query->user_name = isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : __('N/A');
                })
                ->addColumn('user_email', function ($query) {
                    return $query->user_name = isset($query->user) ? $query->user->email : __('N/A');
                })
                ->addColumn('spot_wallet_name', function ($query) {
                    return $query->spot_wallet_name = isset($query->spot_wallet) ? $query->spot_wallet->name : __('N/A');
                })
                ->addColumn('future_wallet_name', function ($query) {
                    return $query->future_wallet_name = isset($query->future_wallet) ? $query->future_wallet->wallet_name : __('N/A');
                })
                ->editColumn('transfer_from', function ($query) {
                    return $query->transfer_from = futureTradeTransformType($query->transfer_from);
                })
                ->editColumn('created_at', function ($query) {
                    return $query->created_at;
                })->make(true);
        }
        return view('admin.future-trade.user.transfer-history-list', $data);
    }

    public function getFutureTradeHistory(Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeLongShort::with('user:id,email')->get();
            return datatables($trade)
                ->editColumn('side', function ($item) {
                    return futureTradeSideList($item->side);
                })
                ->editColumn('user_id', function ($item) {
                    return;
                })
                ->addColumn('action', function ($item) {
                    return '<button class="btn btn-primary" onclick="getTradeDetails(' . $item->id . ')">' . __("Details") . '</button>';
                })
                ->rawColumns(['action'])
                ->make(true);

        }
        $data = [];
        return view('admin.future-trade.trade_history.history', $data);
    }
    public function getFutureTradePositionHistory(Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeLongShort::with(['user:id,email'])->whereNull('parent_id')
                ->where('is_position', STATUS_ACTIVE)
                ->where('status', STATUS_INACTIVE)
                ->get();
            $trade->map(function ($query) {
                $query['profit_loss_calculation'] = calculatePositionData($query->id);
            });
            return datatables($trade)
                ->editColumn('user_id', function ($item) {
                    return $item?->user?->email;
                })
                ->editColumn('symbol', function ($item) {
                    return $item?->profit_loss_calculation['symbol'];
                })
                ->editColumn('size', function ($item) {
                    return $item?->amount_in_trade_coin;
                })
                ->editColumn('entry_price', function ($item) {
                    return $item?->entry_price;
                })
                ->editColumn('market_price', function ($item) {
                    return $item?->profit_loss_calculation['market_price'];
                })
                ->editColumn('liquidation_price', function ($item) {
                    return $item?->liquidation_price;
                })
                ->editColumn('margin_ratio', function ($item) {
                    return $item?->profit_loss_calculation['margin_ratio'];
                })
                ->editColumn('margin', function ($item) {
                    return $item?->margin . ' ' . $item?->profit_loss_calculation['base_coin_type'];
                })
                ->editColumn('pnl', function ($item) {
                    $txt = $item?->profit_loss_calculation['pnl'] . ' ' . $item?->profit_loss_calculation['base_coin_type'] . '<br>';
                    return $txt . $item?->profit_loss_calculation['roe'] . '%';
                })
                ->rawColumns(['action', 'pnl'])
                ->make(true);

        }
        $data = [];
        return view('admin.future-trade.trade_history.position', $data);
    }

    public function getFutureTradeOpenOrderHistory(Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeLongShort::with('user:id,email')
                ->where(function ($query) {
                    $query->orWhere('take_profit_price', '<>', 0)
                        ->orWhere('stop_loss_price', '<>', 0);
                })
                ->where('status', STATUS_INACTIVE)
                ->get();
            $trade->map(function ($query) {
                $query['profit_loss_calculation'] = calculatePositionData($query->id);
                // dd($query);
            });
            return datatables($trade)
                ->editColumn('user_id', function ($item) {
                    return $item?->user?->email;
                })
                ->editColumn('created_at', function ($item) {
                    return date('Y-m-d H:i:s', strtotime($item?->created_at));
                })
                ->editColumn('symbol', function ($item) {
                    return $item?->profit_loss_calculation['symbol'];
                })
                ->editColumn('type', function ($item) {

                    $type = (
                        ($item?->trade_type === FUTURE_TRADE_TYPE_OPEN && $item?->is_market === 0
                            ? __("Limit")
                            : ($item?->trade_type === FUTURE_TRADE_TYPE_CLOSE &&
                                $item?->is_market === 0
                                ? __("Limit")
                                : ($item?->trade_type ===
                                    FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE
                                    ? __("Take profit market")
                                    :
                                    ($item?->trade_type === FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE ? __("Stop market") : __("Market")))))
                    );
                    return $type;

                })
                ->editColumn('side', function ($item) {
                    $txt = ($item?->trade_type == FUTURE_TRADE_TYPE_OPEN) ? __("Open") : __("Close");
                    $txt .= ' ';
                    $txt .= ($item?->side == TRADE_TYPE_BUY) ? __("Long") : __("Short");
                    return $txt;
                })
                ->editColumn('price', function ($item) {
                    return $item?->price . ' ' . $item?->profit_loss_calculation['base_coin_type'];
                })
                ->editColumn('amount', function ($item) {
                    return $item?->amount_in_trade_coin . ' ' . $item?->profit_loss_calculation['trade_coin_type'];
                })
                ->editColumn('triger_condition', function ($item) {
                    return openOrderTrigerCondition($item);
                })

                ->rawColumns(['action', 'pnl'])
                ->make(true);

        }
        $data = [];
        return view('admin.future-trade.trade_history.open_order', $data);
    }
    public function getFutureTradeOrderHistory(Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeLongShort::with('user:id,email')
                ->where(function ($query) {
                    $query->orWhere('take_profit_price', '<>', 0)
                        ->orWhere('stop_loss_price', '<>', 0);
                })
                ->where('status', STATUS_INACTIVE)
                ->get();
            $trade->map(function ($query) {
                $query['profit_loss_calculation'] = calculatePositionData($query->id);
            });
            return datatables($trade)
                ->editColumn('user_id', function ($item) {
                    return $item?->user?->email;
                })
                ->editColumn('created_at', function ($item) {
                    return date('Y-m-d H:i:s', strtotime($item?->created_at));
                })
                ->editColumn('symbol', function ($item) {
                    return $item?->profit_loss_calculation['symbol'];
                })
                ->editColumn('type', function ($item) {

                    $type = (
                        ($item?->trade_type === FUTURE_TRADE_TYPE_OPEN && $item?->is_market === 0
                            ? __("Limit")
                            : ($item?->trade_type === FUTURE_TRADE_TYPE_CLOSE &&
                                $item?->is_market === 0
                                ? __("Limit")
                                : ($item?->trade_type ===
                                    FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE
                                    ? __("Take profit market")
                                    :
                                    ($item?->trade_type === FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE ? __("Stop market") : __("Market")))))
                    );
                    return $type;

                })
                ->editColumn('side', function ($item) {
                    $txt = ($item?->trade_type == FUTURE_TRADE_TYPE_OPEN) ? __("Open") : __("Close");
                    $txt .= ' ';
                    $txt .= ($item?->side == TRADE_TYPE_BUY) ? __("Long") : __("Short");
                    return $txt;
                })
                ->editColumn('price', function ($item) {
                    return $item?->price . ' ' . $item?->profit_loss_calculation['base_coin_type'];
                })
                ->editColumn('amount', function ($item) {
                    return $item?->amount_in_trade_coin . ' ' . $item?->profit_loss_calculation['trade_coin_type'];
                })
                ->editColumn('triger_condition', function ($item) {
                    return openOrderTrigerCondition($item);
                })

                ->rawColumns(['action', 'pnl'])
                ->make(true);

        }
        $data = [];
        return view('admin.future-trade.trade_history.order', $data);
    }
    public function getFutureTradeList(Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeLongShort::with('user:id,email')
                ->whereNotNull('parent_id')
                ->where('is_position', 0)
                ->where(function ($query) {
                    $query->orWhere('take_profit_price', 0)
                        ->orWhere('stop_loss_price', 0);
                })
                ->get();
            $trade->map(function ($query) {
                $query['profit_loss_calculation'] = calculatePositionData($query->id);
            });
            return datatables($trade)
                ->editColumn('user_id', function ($item) {
                    return $item?->user?->email;
                })
                ->editColumn('created_at', function ($item) {
                    return date('Y-m-d H:i:s', strtotime($item?->created_at));
                })
                ->editColumn('symbol', function ($item) {
                    return $item?->profit_loss_calculation['symbol'];
                })
                ->editColumn('fee', function ($item) {

                    $fee = (
                        $item?->trade_type === FUTURE_TRADE_TYPE_OPEN &&
                        $item?->is_market === 0
                        ? __("Limit")
                        : ($item?->trade_type === FUTURE_TRADE_TYPE_CLOSE &&
                            $item?->is_market === 0
                            ? __("Limit")
                            : ($item?->trade_type ===
                                FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE
                                ? __("Take profit market")
                                : ($item?->trade_type === FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE ? __("Stop market") : __("Market"))))
                    );
                    return $fee;

                })
                ->editColumn('side', function ($item) {
                    $txt = ($item?->trade_type == FUTURE_TRADE_TYPE_OPEN) ? __("Open") : __("Close");
                    $txt .= ' ';
                    $txt .= ($item?->side == TRADE_TYPE_BUY) ? __("Long") : __("Short");
                    return $txt;
                })
                ->editColumn('price', function ($item) {
                    return $item?->price . ' ' . $item?->profit_loss_calculation['base_coin_type'];
                })
                ->editColumn('amount', function ($item) {
                    return $item?->amount_in_trade_coin . ' ' . $item?->profit_loss_calculation['trade_coin_type'];
                })
                ->editColumn('role', function ($item) {
                    return __("taker");
                })
                ->editColumn('resized_profit', function ($item) {
                    return $item?->profit_loss_calculation['pnl'] . ' ' . $item?->profit_loss_calculation['base_coin_type'];
                })

                ->rawColumns(['action', 'pnl'])
                ->make(true);

        }
        $data = [];
        return view('admin.future-trade.trade_history.trade', $data);
    }

    public function getFutureTradeDetails($id)
    {
        if ($details = FutureTradeLongShort::find($id)) {
            return responseData(true, __("Trade Details get successfully"), $details);
        }
        return responseData(false, __("Trade not found"));
    }

    public function getFutureTradeTransactionHistory($type, Request $request)
    {
        if ($request->ajax()) {
            $trade = FutureTradeTransactionHistory::with('user:id,email')->whereType($type)->get();
            return datatables($trade)
                ->editColumn('user_id', function ($item) {
                    return $item?->user?->email;
                })
                ->editColumn('amount', function ($item) {
                    return $item->amount . ' ' . $item->coin_type;
                })
                ->make(true);

        }
        $data = [];

        if ($type == FUTURE_TRADE_TRANSACTION_TYPE_TRANSFER)
            $data['header'] = __('Future Trade Transaction History');

        if ($type == FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION)
            $data['header'] = __('Future Trade Commission History');

        if ($type == FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES)
            $data['header'] = __('Future Trade funding Fees History');

        if ($type == FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL)
            $data['header'] = __('Future Trade Realized PNL History');


        $data['sub_menu'] = 'futureTradeTransactionHistory-' . $type;
        return view('admin.future-trade.transaction_history.history', $data);
    }

    public function getFutureTradeTransactionDetails($id)
    {
        if ($details = FutureTradeTransactionHistory::find($id)) {
            return responseData(true, __("Trade Details get successfully"), $details);
        }
        return responseData(false, __("Trade not found"));
    }
}
