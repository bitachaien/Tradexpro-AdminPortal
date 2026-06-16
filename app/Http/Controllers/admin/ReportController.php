<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Exports\OrderHistory;
use App\Exports\BuyOrderHistory;
use App\Exports\TradeTransaction;
use App\Model\TradeReferralHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Services\BuyOrderService;
use App\Http\Services\SellOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Services\TransactionService;
use App\Http\Services\TradeReferralService;
use App\Http\Requests\Admin\TransactionExportRequest;

class ReportController extends Controller
{
    /*
  *
  * All Stop Limit Orders History
  * adminAllOrdersHistoryStopLimit
  *
  * Show the list of specified resource.
  * @return \Illuminate\Http\Response
  *
  */
    public function adminAllOrdersHistoryStopLimit(Request $request)
    {
        $data['title'] = __('Stop Limit Order History');
        $service = new StopLimitService();
        $data['type'] = 'stop_limit';
        $data['sub_menu'] = 'stop_limit';

        if ($request->ajax()) {

            $data['items'] = $service->getOrders();

            return datatables($data['items'])
                ->addColumn('order_type', function ($item) {
                    return ucfirst($item->order_type);
                })
                ->editColumn('price', function ($item) {
                    return $item->price.' '.$item->base_coin;
                })
                ->editColumn('amount', function ($item) {
                    return $item->amount.' '.$item->trade_coin;
                })

                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('order_type', function ($item) {
                    if($item->order_type == 'Sell') {
                        return '<span class="text-success">'.__('Sell').' </span>';
                    } else {
                        return '<span class="text-danger">'.__('Buy').' </span>';
                    }
                })
                ->rawColumns(['order_type'])
                ->make(true);
        }

        return view('admin.exchange.report.stop_limit_order_report',$data);
    }
  /*
  *
  * All Buy Orders History
  * adminAllOrdersHistoryBuy
  *
  * Show the list of specified resource.
  * @return \Illuminate\Http\Response
  *
  */
    public function adminAllOrdersHistoryBuy(Request $request)
    {
        $data['title'] = __('Buy Order History');
        $buyService = new BuyOrderService();
        $data['type'] = 'buy';
        $data['sub_menu'] = 'buy_order';

        if ($request->ajax()) {

            $data['items'] = $buyService->getOrders();

            return datatables($data['items'])
                ->editColumn('is_market', function ($item) {
                    return $item->is_market ? 'Market' : 'Normal';
                })
                ->editColumn('price', function ($item) {
                    return $item->price.' '.$item->base_coin;
                })
                ->editColumn('amount', function ($item) {
                    return $item->amount.' '.$item->trade_coin;
                })
                ->editColumn('processed', function ($item) {
                    return $item->processed.' '.$item->trade_coin;
                })
                ->editColumn('remaining', function ($item) {
                    return $item->remaining.' '.$item->trade_coin;
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('status', function ($item) {
                    if($item->status == 1) {
                        return '<span class="text-success">'.__('Completed').' </span>';
                    } elseif($item->deleted_at != null) {
                        return '<span class="text-warning">'.__('Processing').' </span>';
                    } elseif($item->status == 0) {
                        return '<span class="text-warning">'.__('Pending').' </span>';
                    } else {
                        return '<span class="text-danger">'.__('Deleted').' </span>';
                    }
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('admin.exchange.report.buy_order_report',$data);
    }

    /*
   *
   * All Sell Orders History
   * adminAllOrdersHistorySell
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminAllOrdersHistorySell(Request $request)
    {
        $data['title'] = __('Sell Order History');
        $data['type'] = 'sell';
        $data['sub_menu'] = 'sell_order';
        $sellService = new SellOrderService();

        if ($request->ajax()) {
            $data['items'] = $sellService->getOrders();

            return datatables($data['items'])
                ->editColumn('is_market', function ($item) {
                    return $item->is_market ? 'Market' : 'Normal';
                })
                ->editColumn('price', function ($item) {
                    return $item->price.' '.$item->base_coin;
                })
                ->editColumn('amount', function ($item) {
                    return $item->amount.' '.$item->trade_coin;
                })
                ->editColumn('processed', function ($item) {
                    return $item->processed.' '.$item->trade_coin;
                })
                ->editColumn('remaining', function ($item) {
                    return $item->remaining.' '.$item->trade_coin;
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->editColumn('status', function ($item) {
                    if($item->status == 1) {
                        return '<span class="text-success">'.__('Completed').' </span>';
                    } elseif($item->deleted_at != null) {
                        return '<span class="text-warning">'.__('Processing').' </span>';
                    } elseif($item->status == 0) {
                        return '<span class="text-warning">'.__('Pending').' </span>';
                    } else {
                        return '<span class="text-danger">'.__('Deleted').' </span>';
                    }
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('admin.exchange.report.sell_order_report',$data);
    }

    /*
   *
   * All Sell buy transaction Orders History
   * adminAllTransactionHistory
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminAllTransactionHistory(Request $request)
    {
        $data['title'] = __('Transaction History');
        $data['sub_menu'] = 'transaction';
        try{
            $sellService = new TransactionService();
            if ($request->ajax()) {
                $type = $request->columns[9]["search"]["value"] ?? false;
                $data['items'] = $sellService->getOrdersQueryReport($type ?? 'all');

                return datatables($data['items'])
                    ->filterColumn('transaction_id', function ($query, $keyword) {
                        $query->where('transactions.transaction_id', 'LIKE', "%$keyword%");
                    })
                    ->filterColumn('base_coin', function ($query, $keyword) {
                        $query->where('base_coin_table.coin_type', 'LIKE', "%$keyword%");
                    })
                    ->filterColumn('trade_coin', function ($query, $keyword) {
                        $query->where('trade_coin_table.coin_type', 'LIKE', "%$keyword%");
                    })
                    ->filterColumn('sell_user_email', function ($query, $keyword) {
                        $query->where('sell_user.email', 'LIKE', "%$keyword%");
                    })
                    ->filterColumn('buy_user_email', function ($query, $keyword) {
                        $query->where('buy_user.email', 'LIKE', "%$keyword%");
                    })
                    ->editColumn('price', function ($item) {
                        return $item->price.' '.$item->base_coin;
                    })
                    ->editColumn('total', function ($item) {
                        return number_format($item->total,8).' '.$item->base_coin;
                    })
                    ->editColumn('amount', function ($item) {
                        return $item->amount.' '.$item->trade_coin;
                    })

                    ->editColumn('created_at', function ($item) {
                        return $item->created_at;
                    })
                    ->editColumn('type', function ($item) {
                        return 1;
                    })
                    ->make(true);
            }
        }catch(\Exception $e){
            storeException('adminAllTransactionHistory', $e->getMessage());
        }
        return view('admin.exchange.report.transaction_report',$data);
    }

    public function adminAllTradeReferralHistory(Request $request)
    {
        $data['title'] = __('Trade Referral Distribution History');
        $data['sub_menu'] = 'referral';

        if ($request->ajax()) {
            $referral_history_list = TradeReferralHistory::join('transactions', 'transactions.id','=','trade_referral_histories.transaction_id')
                                                    ->join('users as reference_user', 'reference_user.id','=','trade_referral_histories.user_id')
                                                    ->join('users as referral_user', 'referral_user.id','=','trade_referral_histories.trade_by')
                                                    ->latest()->select('trade_referral_histories.*','transactions.transaction_id as transaction_ref',
                                                        'reference_user.email as reference_user_email','referral_user.email as referral_user_email' );

            return datatables($referral_history_list)
                ->editColumn('created_at', function ($item){
                    return $item->created_at;
                })
                ->editColumn('amount', function ($item) {
                    return $item->amount.' '.$item->coin_type;
                })
                ->make(true);
        }
        return view('admin.exchange.report.trade_referral_history', $data);
    }

    public function adminAllOrdersHistoryBuyExport(TransactionExportRequest $request)
    {
        try{
            return Excel::download(new BuyOrderHistory($request), 'BuyTrade'.($request->export_to ?? '.csv'));
        }catch(\Exception $e){
            storeException('adminAllOrdersHistoryBuyExport', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
    public function adminAllOrdersHistorySellExport(TransactionExportRequest $request)
    {
        try{
            return Excel::download(new BuyOrderHistory($request), 'SellTrade'.($request->export_to ?? '.csv'));
        }catch(\Exception $e){
            storeException('adminAllOrdersHistoryBuyExport', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminAllTransactionHistoryExport(TransactionExportRequest $request)
    {
        try{
            return Excel::download(new TradeTransaction($request), 'TransactionHistory'.($request->export_to ?? '.csv'));
        }catch(\Exception $e){
            storeException('adminAllOrdersHistoryBuyExport', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
}
