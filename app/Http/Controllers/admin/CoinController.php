<?php

namespace App\Http\Controllers\admin;

use App\Exceptions\InvalidRequestException;
use App\Http\Repositories\AffiliateRepository;
use App\Http\Requests\Admin\CoinRequest;
use App\Http\Requests\Admin\CoinSaveRequest;
use App\Http\Requests\Admin\CoinSettingRequest;
use App\Http\Requests\Admin\GiveCoinRequest;
use App\Http\Requests\Admin\WebhookRequest;
use App\Http\Requests\UpdateWalletKeyRequest;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\CoinService;
use App\Http\Services\CoinSettingService;
use App\Http\Services\CurrencyService;
use App\Http\Services\Logger;
use App\Jobs\AdjustWalletJob;
use App\Jobs\BulkWalletGenerateJob;
use App\Jobs\NewCoinCreateJob;
use App\Model\AdminGiveCoinHistory;
use App\Model\BuyCoinHistory;
use App\Model\Coin;
use App\Model\CurrencyList;
use App\Model\Wallet;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\DataTable\CoinDataTableService;
use App\Http\Services\ERC20TokenApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;


class CoinController extends Controller
{
    private $coinService;
    private $coinSettingService;
    public function __construct()
    {
        $this->coinService = new CoinService();
        $this->coinSettingService = new CoinSettingService();
    }


    // all coin list
    public function adminCoinList(Request $request)
    {
        try {
            $check_module = Module::allEnabled();

            $data['module'] = $check_module;
            $data['title'] = __('Coin List');
            if ($request->ajax()) {
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
                        return number_format($item->coin_price, 2) . ' USD/ ' . $item->coin_type;
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

            return view('admin.coin-order.coin', $data);
        } catch (\Exception $e) {
            storeException('admin coin-list exception --> ', $e->getMessage());
            return redirect()->back()->with('dismiss', $e->getMessage());
        }
    }

    // change coin status
    public function adminCoinStatus(Request $request)
    {
        $coin = Coin::find($request->active_id);
        if ($coin) {
            if ($coin->status == STATUS_ACTIVE) {
                $coin->update(['status' => STATUS_INACTIVE]);
            } else {
                $coin->update(['status' => STATUS_ACTIVE]);
            }
            return response()->json(['message' => __('Status changed successfully')]);
        } else {
            return response()->json(['message' => __('Coin not found')]);
        }
    }

    // edit coin
    public function adminCoinEdit($id)
    {
        $coinId = decryptId($id);

        if (is_array($coinId)) {
            return back()->with(['dismiss' => __('Coin not found')]);
        }
        $data = array_merge([
            'module' => Module::allEnabled(),
            'item' => Coin::find($coinId),
            'title' => __('Update Coin'),
            'button_title' => __('Update')
        ]);
        return view('admin.coin-order.edit_coin', $data);
    }


    //    coin save process
    public function adminCoinUpdateProcess(CoinRequest $request)
    {
        return $this->handlerResponseAndRedirect(function () use ($request) {
            $coin_id = '';
            if ($request->coin_id) {
                $coin_id = decryptId($request->coin_id);
            }
            $response = $this->coinService->updateCoin($request, $coin_id);

            if ($response['data']['updateNetwork'] == true)
                $response['data']['redirectUrl'] = route('adminCoinSettings', encrypt($coin_id));

            return $response;
        });
    }

    // add coin page
    public function adminAddCoin()
    {
        $data = [
            'title' => __('Add New Coin'),
            'button_title' => __('Save'),
            'currency' => CurrencyList::where('status', STATUS_ACTIVE)->get()
        ];
        return view('admin.coin-order.add_coin', $data);
    }

    // admin new coin save process
    public function adminSaveCoin(CoinSaveRequest $request)
    {
        try {
            $data = [
                'currency_type' => $request->currency_type,
                'name' => $request->name,
                'coin_type' => strtoupper($request->coin_type),
                'network' => $request->network,
            ];
            if ($request->currency_type == CURRENCY_TYPE_FIAT) {
                if ($currency = CurrencyList::whereCode($request->coin_type)->first()) {
                    $data['currency_id'] = $currency->id;
                    $data['coin_price'] = bcdivx(1, $currency->rate, 8);
                }
            } else {
                if ($request->get_price_api == 1) {
                    $pair = strtoupper($request->coin_type) . '_' . 'USDT';
                    $apiData = getPriceFromApi($pair);
                    if ($apiData['success'] == true) {
                        $data['coin_price'] = $apiData['data']['price'];
                    } else {
                        return redirect()->back()->with('dismiss', __('Get api data failed, please add manual price'));
                    }
                } else {
                    $data['coin_price'] = $request->coin_price;
                }
            }
            $coin = Coin::create($data);
            BulkWalletGenerateJob::dispatch($coin->id, WALLET_GENERATE_BY_COIN);

            if ($coin) {
                return redirect()->route('adminCoinList')->with('dismiss', __('New coin added successfully'));
            }
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        } catch (\Exception $e) {
            storeException('adminSaveCoin : ', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // edit coin settings
    public function adminCoinSettings($id)
    {
        return $this->handlerResponseAndRedirect(function () use ($id) {
            $coinId = decryptId($id);
            if (is_array($coinId))
                throw new InvalidRequestException(__('Invalid coin'));

            $coinSetting = $this->coinSettingService->getCoinSettings($coinId);

            $data = array_merge([
                'item' => $coinSetting,
                'title' => __('Update Coin Setting'),
                'button_title' => __('Update Setting')
            ]);

            if ($coinSetting->network == COIN_PAYMENT) {
                $data['redirectUrl'] = route('adminCoinApiSettings', ['tab' => 'payment']);
            } else {
                $data['redirectView'] = 'admin.coin-order.edit_coin_settings';
            }

            return $this->responseData(true, '', $data);
        });
    }

    // admin save coin setting
    public function adminSaveCoinSetting(CoinSettingRequest $request)
    {
        try {
            $response = $this->coinSettingService->updateCoinSetting($request);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminSaveCoinSetting', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // admin bitgo wallet adjust
    public function adminAdjustBitgoWallet($id)
    {

        try {
            $coinId = decryptId($id);
            if (is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin not found')]);
            }
            $response = $this->coinSettingService->adjustBitgoWallet($coinId);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminAdjustBitgoWallet', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminCoinRate()
    {

        $currency = new CurrencyService();
        $response = $currency->updateCoinRate();
        if ($response["success"])
            return redirect()->back()->with("success", $response["message"]);
        return redirect()->back()->with("dismiss", $response["message"]);
    }

    // admin coin delete
    public function adminCoinDelete($id)
    {

        try {
            $coinId = decryptId($id);
            if (is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin not found')]);
            }
            $response = $this->coinService->adminCoinDeleteProcess($coinId);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminCoinDelete', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // admin user coin
    public function adminUserCoinList()
    {
        $data['title'] = __('User Total Coin Amount');
        $data['items'] = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['coins.status' => STATUS_ACTIVE])
            ->selectRaw('sum(wallets.balance) as total_balance, coins.coin_type, coins.name')
            ->groupBy('coins.id')
            ->get();

        return view('admin.coin-order.user_coin', $data);
    }

    public function webhookSave(WebhookRequest $request)
    {
        try {
            $response = $this->coinService->webhookSaveProcess($request);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('webhookSave: ', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function check_wallet_address(Request $request)
    {
        try {
            if (!isset($request->coin_type)) {
                $response = ['success' => false, 'message' => __('Coin type is missing!')];
                return response()->json($response);
            }
            if (!isset($request->wallet_key)) {
                $response = ['success' => false, 'message' => __('Wallet key is missing!')];
                return response()->json($response);
            }
            $coin_type = $request->coin_type;

            $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $coin_type])
                ->first();

            if (isset($coin)) {
                $api = new ERC20TokenApi($coin);
                $requestData = ['contracts' => $request->wallet_key];
                $result = $api->getAddressFromPK($requestData);
                if ($result['success']) {
                    $response = ['success' => true, 'message' => __('Your wallet address: ') . $result['data']->address];
                } else {
                    $response = ['success' => false, 'message' => $result['message']];
                }
            } else {
                $response = ['success' => false, 'message' => __('Invalid Request: ')];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('webhookSave: ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
            return response()->json($response);
        }
    }

    public function coinMakeListed($id)
    {
        $response = $this->coinService->makeTokenListedToCoin(decrypt($id));
        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function updateWalletKey(UpdateWalletKeyRequest $request)
    {
        $response = $this->coinService->updateWalletKey($request);

        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function viewWalletKey(Request $request)
    {
        $response = $this->coinService->viewWalletKey($request);
        return response()->json($response);
    }

    public function demoTradeCoinStatus($coin_type)
    {
        try {
            if ($coin = Coin::whereCoinType($coin_type)->first()) {
                if ($coin->update(['is_demo_trade' => (!$coin->is_demo_trade)]))
                    return response()->json(responseData(true, __("Coin status updated")));
                return response()->json(responseData(false, __("Coin not updated")));
            }
            return response()->json(responseData(false, __("Coin not found")));
        } catch (\Exception $e) {
            storeException('demoTradeCoinStatus', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }
}
