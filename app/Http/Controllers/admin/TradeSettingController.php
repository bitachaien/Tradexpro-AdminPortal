<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\Admin\CoinPairRequest;
use App\Http\Requests\FutureCoinPairRequest;
use App\Http\Services\AdminSettingService;
use App\Model\AdminSetting;
use App\Model\Coin;
use App\Model\CoinPair;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;
use App\Http\Services\UserService;
use App\Services\TradeSettingServices\TradeSettingService;
use Throwable;

class TradeSettingController extends Controller
{
    private $coinPairService;

    public function __construct()
    {
        $this->coinPairService = new CoinPairService;
    }

    /*
   *
   * coin pair List
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function coinPairs(Request $request)
    {
        $data['title'] = __('Coin Pair List');
        $coins = Coin::where(['is_base'=>STATUS_ACTIVE, 'trade_status'=>STATUS_ACTIVE, 'status'=>STATUS_ACTIVE])->get();
        $data['coins'] = $coins;
        $global_enable_future_trade = AdminSetting::where('slug', 'enable_future_trade')->first();
        $data['global_enable_future_trade'] = isset($global_enable_future_trade->value) ? $global_enable_future_trade->value : 0;
        if ($request->ajax()) {
            $search = $request->search["value"] ?? false;
            $items = CoinPair::select('coin_pairs.id as id','parent_coin_id','child_coin_id','coin_pairs.volume',
            'coin_pairs.bot_trading','coin_pairs.price','coin_pairs.change',"high","low",'coin_pairs.is_token',
            'coin_pairs.is_default','coin_pairs.created_at','coin_pairs.status','coin_pairs.enable_future_trade',
            'coin_pairs.is_chart_updated','coin_pairs.pair_decimal',
            'child_coin.coin_type as child_coin_name','parent_coin.coin_type as parent_coin_name')
            ->join('coins as child_coin',  'coin_pairs.child_coin_id', "=", 'child_coin.id')
            ->join('coins as parent_coin', 'coin_pairs.parent_coin_id', "=", 'parent_coin.id')
            ->when($search, function($query) use($search){
                return $query->where("child_coin.coin_type", "LIKE", "$search%")
                ->orWhere("parent_coin.coin_type", "LIKE", "$search%");
            });
            
            return datatables()->of($items)
            ->addColumn('parent_coin_name', function($item) {
                return $item->parent_coin_name;
            })
            ->addColumn('child_coin_name', function($item) {
                return $item->child_coin_name;
            })
            ->addColumn('created_at', function($item) {
                return $item->created_at;
            })
            ->addColumn('price', function($item) {
                return $item->price .' '.$item->parent_coin_name;
            })
            ->addColumn('status', function($item) {
                $data['item'] = $item;
                return view('admin.exchange.coin_pair.switch.status_switch', $data);
            })
            ->addColumn('is_default', function($item) {
                $data['item'] = $item;
                return view('admin.exchange.coin_pair.switch.default_switch', $data);
            })
            ->addColumn('bot_trading', function($item) {
                $data['item'] = $item;
                return view('admin.exchange.coin_pair.switch.bot_switch', $data);
            })
            ->addColumn('enable_future_trade', function($item) {
                $data['item'] = $item;
                return view('admin.exchange.coin_pair.switch.future_switch', $data);
            })
            ->addColumn('is_token', function($item) {
                return $item->is_token == STATUS_ACTIVE ? '<span class="text-danger"> No </span>' : '<span class="text-success"> Yes </span>';
            })
            ->addColumn('actions', function($item) use ($coins) {
                $data['coins'] = $coins;
                $data['item'] = $item;
                return view('admin.exchange.coin_pair.switch.actions', $data);
            })
            ->rawColumns(['status','is_default','bot_trading','enable_future_trade','is_token','actions'])
            ->make(true);
        }
        // $data['coins'] = Coin::where(['is_base'=>STATUS_ACTIVE, 'trade_status'=>STATUS_ACTIVE, 'status'=>STATUS_ACTIVE])->get();

        // $data['items'] = CoinPair::orderBy('id','desc')->get();

        return view('admin.exchange.coin_pair.list', $data);
    }

    /**
     * saveCoinPairSettings
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function saveCoinPairSettings(CoinPairRequest $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->savePairSetting($request);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }

    /**
     * changeCoinPairStatus
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function changeCoinPairStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function changeCoinPairDefaultStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairDefaultStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function changeCoinPairBotStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairBotStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }


    public function coinPairsDelete($id)
    {
        try {
            $coinId = decryptId($id);
            if(is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
            }
            $adminSettingService = new AdminSettingService();
            $update = $adminSettingService->coinPairsDeleteProcess($coinId);
            if ($update['success'] == true) {
                return redirect()->back()->with(['success' => $update['message']]);
            } else {
                return redirect()->back()->with(['dismiss' => $update['message']]);
            }
        } catch (\Exception $e) {
            storeException('coinPairsDelete', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    /**
     * tradeFeesSettings
     *
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function tradeFeesSettings(
        TradeSettingService $tradeSettingService,
        UserService $userService,
        CoinPairService $coinPairService
    )
    {
        $data = [
            'settings' => $tradeSettingService->getFeeSetting(),
            'coinPairs' => $coinPairService->getCoinPairIdAndCoins(),
            'users' => $userService->getUserIdandEmails()
        ];

        return view('admin.exchange.trade.trade_fees_settings', $data);
    }

    /**
     * tradeFeesSettingSave
     *
     * Store a newly created resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */

    public function tradeFeesSettingSave(Request $request)
    {
        $fields = [];
        foreach ($request->except('_token') as $key => $part) {
            $fields[] = $key;
        }
        $rules = array_fill_keys($fields, ['numeric']);
        $this->validate($request, $rules);
        if (!isset($request->trade_limit_1) || $request->trade_limit_1 != 0) {
            return redirect()->back()->with(['dismiss' => __('First limit must be 0')]);
        }
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->tradeSetting($request->except('_token'));
        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }


    /**
     * removeTradeLimit
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function removeTradeLimit(Request $request)
    {
        if ($request->id == 1) {
            return response()->json([
                'status' => false,
                'message' => __('First Limit can not be removed')
            ]);
        }
        $limits = AdminSetting::where('slug', 'like', '%_' . $request->id)->get();
        foreach ($limits as $limit) {
            $limit->forceDelete();
        }

        return response()->json([
            'status' => true,
            'message' => __('Limit is removed successfully')
        ]);
    }

    public function coinPairsChartUpdate($id)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->coinPairsChartUpdate($id);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }

    public function changeFutureTradeStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeFutureTradeStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function coinPairFutureSetting($id)
    {
        $data['title'] = __('Coin Pair settings');

        $response = $this->coinPairService->getCoinPairDetails(decrypt($id));

        if($response['success'])
        {
            $data['coin_pair_details']  = $response['data'];

            return view('admin.exchange.coin_pair.settings', $data);
        }

        return back()->with(['dismiss' => $response['message']]);

    }

    public function coinPairFutureSettingUpdate(FutureCoinPairRequest $request)
    {
        $response = $this->coinPairService->coinPairFutureSettingUpdate($request);
        if($response['success'])
        {
            return redirect()->back()->with(['success'=>$response['message']]);
        }else{
            return back()->with(['dismiss'=>$response['message']]);
        }
    }

}
